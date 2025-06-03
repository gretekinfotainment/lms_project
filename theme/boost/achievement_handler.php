<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Handle AJAX operations for the achievements section
 *
 * @package    theme_boost
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');
require_once($CFG->dirroot . '/repository/lib.php');

// Log access for debugging
error_log("Accessing achievement_handler.php at " . date('Y-m-d H:i:s'));

$action = required_param('action', PARAM_ALPHA);
$PAGE->set_context(context_system::instance());

require_login();
require_capability('moodle/site:config', context_system::instance());

// Setup AJAX response
header('Content-Type: application/json');

switch ($action) {
    case 'get':
        // Fetch all achievements
        try {
            $achievements = $DB->get_records('theme_achievements', null, 'id ASC');
            $result = array();
            
            foreach ($achievements as $achievement) {
                $result[] = array(
                    'id' => (string)$achievement->id, // Ensure ID is string for JavaScript
                    'category' => $achievement->category ?? 'Uncategorized',
                    'title' => $achievement->title,
                    'description' => $achievement->description,
                    'image' => $achievement->imagepath ?? ''
                );
            }
            
            echo json_encode(['status' => 'success', 'data' => $result]);
        } catch (Exception $e) {
            error_log("Error in get action: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Failed to fetch achievements']);
        }
        break;
        
    case 'add':
        // Add a new achievement
        require_sesskey();
        
        $category = required_param('category', PARAM_TEXT);
        $title = required_param('title', PARAM_TEXT);
        $description = required_param('description', PARAM_TEXT);
        
        // Create file storage directory if it doesn't exist
        $context = context_system::instance();
        $fs = get_file_storage();
        
        // Handle image upload
        $success = false;
        $imagepath = '';
        
        if (!empty($_FILES['image']['name'])) {
            $filename = $_FILES['image']['name'];
            $tempfile = $_FILES['image']['tmp_name'];
            
            // Prepare file record
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'theme_boost',
                'filearea' => 'achievement_images',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $filename
            );
            
            // Create or update the file
            if (file_exists($tempfile)) {
                if ($fs->file_exists($fileinfo['contextid'], $fileinfo['component'], 
                                    $fileinfo['filearea'], $fileinfo['itemid'], 
                                    $fileinfo['filepath'], $fileinfo['filename'])) {
                    $file = $fs->get_file(
                        $fileinfo['contextid'], $fileinfo['component'],
                        $fileinfo['filearea'], $fileinfo['itemid'],
                        $fileinfo['filepath'], $fileinfo['filename']
                    );
                    $file->delete();
                }
                
                $file = $fs->create_file_from_pathname($fileinfo, $tempfile);
                $success = true;
                
                // Create URL to the stored file
                $url = moodle_url::make_pluginfile_url(
                    $fileinfo['contextid'],
                    $fileinfo['component'],
                    $fileinfo['filearea'],
                    $fileinfo['itemid'],
                    $fileinfo['filepath'],
                    $fileinfo['filename']
                );
                $imagepath = $url->out();
            } else {
                error_log("Image upload failed: Temporary file does not exist: $tempfile");
            }
        } else {
            error_log("No image uploaded for add action");
        }
        
        if ($success) {
            $achievement = new stdClass();
            $achievement->category = $category;
            $achievement->title = $title;
            $achievement->description = $description;
            $achievement->imagepath = $imagepath;
            $achievement->timecreated = time();
            $achievement->timemodified = time();
            $achievement->usermodified = $USER->id;
            
            try {
                $id = $DB->insert_record('theme_achievements', $achievement);
                
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Achievement added successfully',
                    'data' => [
                        'id' => (string)$id,
                        'category' => $achievement->category,
                        'title' => $achievement->title,
                        'description' => $achievement->description,
                        'image' => $achievement->imagepath,
                    ]
                ]);
            } catch (Exception $e) {
                error_log("Error in add action: " . $e->getMessage());
                echo json_encode(['status' => 'error', 'message' => 'Failed to save achievement']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to upload image']);
        }
        break;
        
    case 'edit':
        // Update an existing achievement
        require_sesskey();
        
        $id = required_param('id', PARAM_INT);
        $category = required_param('category', PARAM_TEXT);
        $title = required_param('title', PARAM_TEXT);
        $description = required_param('description', PARAM_TEXT);
        
        $existing = $DB->get_record('theme_achievements', ['id' => $id]);
        if (!$existing) {
            echo json_encode(['status' => 'error', 'message' => 'Achievement not found']);
            break;
        }
        
        $context = context_system::instance();
        $fs = get_file_storage();
        $imagepath = $existing->imagepath;
        
        // Handle image upload if provided
        if (!empty($_FILES['image']['name'])) {
            $filename = $_FILES['image']['name'];
            $tempfile = $_FILES['image']['tmp_name'];
            
            // Prepare file record
            $fileinfo = array(
                'contextid' => $context->id,
                'component' => 'theme_boost',
                'filearea' => 'achievement_images',
                'itemid' => $id,
                'filepath' => '/',
                'filename' => $filename
            );
            
            // Create or update the file
            if (file_exists($tempfile)) {
                if ($fs->file_exists($fileinfo['contextid'], $fileinfo['component'], 
                                    $fileinfo['filearea'], $fileinfo['itemid'], 
                                    $fileinfo['filepath'], $fileinfo['filename'])) {
                    $file = $fs->get_file(
                        $fileinfo['contextid'], $fileinfo['component'],
                        $fileinfo['filearea'], $fileinfo['itemid'],
                        $fileinfo['filepath'], $fileinfo['filename']
                    );
                    $file->delete();
                }
                
                $file = $fs->create_file_from_pathname($fileinfo, $tempfile);
                
                // Create URL to the stored file
                $url = moodle_url::make_pluginfile_url(
                    $fileinfo['contextid'],
                    $fileinfo['component'],
                    $fileinfo['filearea'],
                    $fileinfo['itemid'],
                    $fileinfo['filepath'],
                    $fileinfo['filename']
                );
                $imagepath = $url->out();
            } else {
                error_log("Image upload failed in edit action: Temporary file does not exist: $tempfile");
            }
        }
        
        $achievement = new stdClass();
        $achievement->id = $id;
        $achievement->category = $category;
        $achievement->title = $title;
        $achievement->description = $description;
        $achievement->imagepath = $imagepath;
        $achievement->timemodified = time();
        $achievement->usermodified = $USER->id;
        
        try {
            $DB->update_record('theme_achievements', $achievement);
            
            echo json_encode([
                'status' => 'success',
                'message' => 'Achievement updated successfully',
                'data' => [
                    'id' => (string)$id,
                    'category' => $achievement->category,
                    'title' => $achievement->title,
                    'description' => $achievement->description,
                    'image' => $achievement->imagepath,
                ]
            ]);
        } catch (Exception $e) {
            error_log("Error in edit action: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Failed to update achievement']);
        }
        break;
        
    case 'delete':
        // Delete an achievement
        require_sesskey();
        
        $id = required_param('id', PARAM_INT);
        
        try {
            // Delete associated files
            $context = context_system::instance();
            $fs = get_file_storage();
            $fs->delete_area_files($context->id, 'theme_boost', 'achievement_images', $id);
            
            // Delete the record
            $DB->delete_records('theme_achievements', ['id' => $id]);
            
            echo json_encode(['status' => 'success', 'message' => 'Achievement deleted successfully']);
        } catch (Exception $e) {
            error_log("Error in delete action: " . $e->getMessage());
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete achievement']);
        }
        break;
        
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
}