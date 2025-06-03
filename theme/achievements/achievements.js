document.addEventListener('DOMContentLoaded', function() {
    const handlerUrl = M.cfg.wwwroot + '/theme/boost/achievement_handler.php';
    const achievementsGrid = document.querySelector('.achievements-grid');
    const canEdit = document.querySelector('.add-achievement-card') !== null;

    function initAchievements() {
        if (!achievementsGrid) {
            console.error('Achievements grid not found in the DOM');
            return;
        }

        if (canEdit) {
            loadAchievementsFromDB();
        }
        
        attachEventHandlers();
    }

    function loadAchievementsFromDB() {
        fetch(handlerUrl + '?action=get')
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    const existingCards = document.querySelectorAll('.achievement-card:not(.add-achievement-card)');
                    existingCards.forEach(card => card.remove());
                    
                    result.data.forEach(achievement => {
                        addAchievementToDOM(achievement);
                    });
                }
            })
            .catch(error => console.error('Error loading achievements:', error));
    }

    function attachEventHandlers() {
        document.body.addEventListener('click', function(e) {
            const target = e.target;

            if (target.classList.contains('add-achievement-btn')) {
                e.preventDefault();
                openAchievementModal();
            }

            if (target.classList.contains('edit-achievement-btn')) {
                e.preventDefault();
                const id = target.getAttribute('data-id');
                fetch(handlerUrl + '?action=get')
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            const achievement = result.data.find(a => String(a.id) === String(id));
                            if (achievement) {
                                openAchievementModal(achievement, false);
                            } else {
                                showErrorMessage(`Achievement not found for ID: ${id}.`);
                            }
                        } else {
                            showErrorMessage('Error fetching achievements: ' + result.message);
                        }
                    })
                    .catch(error => {
                        showErrorMessage('An error occurred while fetching the achievement.');
                    });
            }

            if (target.classList.contains('delete-achievement-btn')) {
                e.preventDefault();
                const id = target.getAttribute('data-id');
                if (confirm('Are you sure you want to delete this achievement?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    formData.append('sesskey', M.cfg.sesskey);
                    
                    fetch(handlerUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(result => {
                        if (result.status === 'success') {
                            const card = target.closest('.achievement-card');
                            if (card) {
                                card.remove();
                                showSuccessMessage('Achievement deleted successfully!');
                            }
                        } else {
                            showErrorMessage('Failed to delete achievement: ' + result.message);
                        }
                    })
                    .catch(error => {
                        showErrorMessage('An error occurred while deleting the achievement.');
                    });
                }
            }
        });
    }

    function addAchievementToDOM(achievement) {
        const addCard = document.querySelector('.add-achievement-card');
        if (!addCard || !achievementsGrid) return;

        const newCard = document.createElement('div');
        newCard.className = 'achievement-card animate-fade-in';
        newCard.setAttribute('data-id', achievement.id);
        
        // Updated card structure to match Image 2 style
        newCard.innerHTML = `
            <img src="${achievement.image}" alt="${achievement.category}">
            <div class="achievement-caption">
                <h3>${achievement.category}</h3>
                <p>${achievement.title}</p>
            </div>
            <div class="achievement-actions">
                <button class="edit-achievement-btn" data-id="${achievement.id}">Edit</button>
                <button class="delete-achievement-btn" data-id="${achievement.id}">Delete</button>
            </div>
        `;

        achievementsGrid.insertBefore(newCard, addCard);
    }

    function updateAchievementInDOM(achievement) {
        const card = document.querySelector(`.achievement-card[data-id="${achievement.id}"]`);
        if (!card) return;

        const img = card.querySelector('img');
        const title = card.querySelector('h3');
        const desc = card.querySelector('p');

        if (img) img.src = achievement.image;
        if (img) img.alt = achievement.category;
        if (title) title.textContent = achievement.category;
        if (desc) desc.textContent = achievement.title;
    }

    function openAchievementModal(achievement = null, isNew = true) {
        const modal = document.createElement('div');
        modal.className = 'achievement-editor-modal';
        modal.innerHTML = `
            <div class="achievement-editor-content">
                <div class="achievement-editor-header">
                    <h3>${isNew ? 'Add Achievement' : 'Edit Achievement'}</h3>
                    <button type="button" class="close-editor-btn">×</button>
                </div>
                <div class="achievement-editor-body">
                    <form id="achievementForm" class="achievement-form" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="${isNew ? 'add' : 'edit'}">
                        <input type="hidden" name="sesskey" value="${M.cfg.sesskey}">
                        ${!isNew ? `<input type="hidden" name="id" value="${achievement.id}">` : ''}
                        
                        <div class="achievement-form-group">
                            <label for="achievement-category">Category</label>
                            <select id="achievement-category" name="category">
                                <option value="Data Science" ${achievement && achievement.category === 'Data Science' ? 'selected' : ''}>Data Science</option>
                                <option value="Business" ${achievement && achievement.category === 'Business' ? 'selected' : ''}>Business</option>
                                <option value="Personal Development" ${achievement && achievement.category === 'Personal Development' ? 'selected' : ''}>Personal Development</option>
                                <option value="Computer Science" ${achievement && achievement.category === 'Computer Science' ? 'selected' : ''}>Computer Science</option>
                                <option value="Information Technology" ${achievement && achievement.category === 'Information Technology' ? 'selected' : ''}>Information Technology</option>
                                <option value="Academics" ${achievement && achievement.category === 'Academics' ? 'selected' : ''}>Academics</option>
                                <option value="Co-Curricular" ${achievement && achievement.category === 'Co-Curricular' ? 'selected' : ''}>Co-Curricular</option>
                                <option value="Sports" ${achievement && achievement.category === 'Sports' ? 'selected' : ''}>Sports</option>
                            </select>
                        </div>
                        <div class="achievement-form-group">
                            <label for="achievement-title">Title</label>
                            <input type="text" id="achievement-title" name="title" value="${achievement ? achievement.title : ''}" required>
                        </div>
                        <div class="achievement-form-group">
                            <label for="achievement-description">Description</label>
                            <textarea id="achievement-description" name="description" required>${achievement ? achievement.description : ''}</textarea>
                        </div>
                        <div class="achievement-form-group">
                            <label for="achievement-image">Image</label>
                            <input type="file" id="achievement-image" name="image" accept="image/*" ${isNew ? 'required' : ''}>
                            ${!isNew ? `<p class="current-image">Current image: ${achievement.image.split('/').pop()}</p>` : ''}
                        </div>
                        <div class="achievement-form-actions">
                            <button type="button" class="achievement-cancel-btn">Cancel</button>
                            <button type="submit" class="achievement-save-btn">${isNew ? 'Add' : 'Save'}</button>
                        </div>
                    </form>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        setTimeout(() => {
            modal.style.display = 'block';
        }, 10);

        const closeBtn = modal.querySelector('.close-editor-btn');
        const cancelBtn = modal.querySelector('.achievement-cancel-btn');
        const form = modal.querySelector('#achievementForm');

        function closeModal() {
            modal.style.display = 'none';
            setTimeout(() => {
                modal.remove();
            }, 300);
        }

        closeBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                closeModal();
            }
        });

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            
            fetch(handlerUrl, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.status === 'success') {
                    if (isNew) {
                        addAchievementToDOM(result.data);
                        showSuccessMessage('Achievement added successfully!');
                    } else {
                        updateAchievementInDOM(result.data);
                        showSuccessMessage('Achievement updated successfully!');
                    }
                    closeModal();
                } else {
                    showErrorMessage('Failed to save achievement: ' + result.message);
                }
            })
            .catch(error => {
                showErrorMessage('An error occurred while saving the achievement.');
            });
        });
    }

    function showSuccessMessage(message) {
        const successMsg = document.createElement('div');
        successMsg.className = 'success-message';
        successMsg.textContent = message;
        successMsg.style.display = 'block';
        document.body.appendChild(successMsg);

        setTimeout(() => {
            successMsg.remove();
        }, 3000);
    }

    function showErrorMessage(message) {
        const errorMsg = document.createElement('div');
        errorMsg.className = 'error-message';
        errorMsg.textContent = message;
        errorMsg.style.display = 'block';
        document.body.appendChild(errorMsg);

        setTimeout(() => {
            errorMsg.remove();
        }, 5000);
    }

    initAchievements();
});