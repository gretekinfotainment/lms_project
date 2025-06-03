<?php
    require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
    require_once($CFG->libdir . '/adminlib.php');
    require_login();
    $PAGE->set_url(new moodle_url("/theme/boost/add_achievement.php"));
    $PAGE->set_context(context_system::instance());
    $PAGE->set_title("Add Achievement");
    $PAGE->set_heading("Add Achievement");

    // Check permission
    if (!has_capability("moodle/course:update", context_system::instance())) {
        redirect($CFG->wwwroot);
    }

    // Process form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        require_sesskey();
        
        $category = required_param("category", PARAM_TEXT);
        $title = required_param("title", PARAM_TEXT);
        $description = required_param("description", PARAM_TEXT);
        
        // Handle file upload
        $imagepath = "";
        if (!empty($_FILES["image"]["name"])) {
            $target_dir = $CFG->dirroot . "/theme/achievements/images/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $target_file = $target_dir . basename($_FILES["image"]["name"]);
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            // Check if image file is an actual image
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check !== false) {
                // Generate unique filename
                $filename = uniqid() . "." . $imageFileType;
                $target_file = $target_dir . $filename;
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $imagepath = $CFG->wwwroot . "/theme/achievements/images/" . $filename;
                }
            }
        }
        
        // Save to database
        $record = new stdClass();
        $record->category = $category;
        $record->title = $title;
        $record->description = $description;
        $record->imagepath = $imagepath;
        $record->timemodified = time();
        
        global $DB;
        if ($DB->insert_record("theme_achievements", $record)) {
            redirect($CFG->wwwroot, "Achievement added successfully!", null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            redirect($CFG->wwwroot, "Failed to add achievement.", null, \core\output\notification::NOTIFY_ERROR);
        }
    }

    echo $OUTPUT->header();
    ?>

    <div class="achievement-editor-content" style="max-width: 600px; margin: 0 auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 20px; color: #005a9e; border-bottom: 1px solid #eee; padding-bottom: 10px;">Add Achievement</h3>
        
        <form method="post" enctype="multipart/form-data" class="achievement-form" style="display: grid; gap: 20px;">
            <input type="hidden" name="sesskey" value="<?php echo sesskey(); ?>">
            
            <div style="display: flex; flex-direction: column;">
                <label for="category" style="margin-bottom: 5px; font-weight: bold;">Category</label>
                <select name="category" id="category" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="Academics">Academics</option>
                    <option value="Co-Curricular">Co-Curricular</option>
                    <option value="Sports">Sports</option>
                </select>
            </div>
            
            <div style="display: flex; flex-direction: column;">
                <label for="title" style="margin-bottom: 5px; font-weight: bold;">Title</label>
                <input type="text" name="title" id="title" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="display: flex; flex-direction: column;">
                <label for="description" style="margin-bottom: 5px; font-weight: bold;">Description</label>
                <textarea name="description" id="description" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; min-height: 100px;"></textarea>
            </div>
            
            <div style="display: flex; flex-direction: column;">
                <label for="image" style="margin-bottom: 5px; font-weight: bold;">Image</label>
                <input type="file" name="image" id="image" accept="image/*" required style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
                <a href="<?php echo $CFG->wwwroot; ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>

    <?php
    echo $OUTPUT->footer();
    ?>