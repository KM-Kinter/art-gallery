// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
    
    // Handle file input change
    const fileInputs = document.querySelectorAll('.custom-file-input');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name || 'No file chosen';
            const label = this.nextElementSibling;
            if (label) {
                label.textContent = fileName;
            }
        });
    });
    
    // Handle rating stars
    const ratingContainers = document.querySelectorAll('.rating-stars');
    ratingContainers.forEach(container => {
        const stars = container.querySelectorAll('.star');
        const ratingInput = container.querySelector('input[type="hidden"]');
        
        stars.forEach((star, index) => {
            // Hover effect
            star.addEventListener('mouseover', () => {
                for (let i = 0; i <= index; i++) {
                    stars[i].classList.add('hover');
                }
            });
            
            star.addEventListener('mouseout', () => {
                stars.forEach(s => s.classList.remove('hover'));
            });
            
            // Click event
            star.addEventListener('click', () => {
                const rating = index + 1;
                if (ratingInput) {
                    ratingInput.value = rating;
                }
                
                stars.forEach((s, i) => {
                    s.classList.toggle('active', i < rating);
                });
                
                // If the star is part of a form, submit it
                const form = container.closest('form');
                if (form && form.classList.contains('auto-submit')) {
                    form.submit();
                }
            });
        });
    });
    
    // Handle follow/unfollow buttons
    const followButtons = document.querySelectorAll('.follow-btn');
    followButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const artistId = this.dataset.artistId;
            const isFollowing = this.classList.contains('following');
            
            fetch('/ajax/follow.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    artist_id: artistId,
                    action: isFollowing ? 'unfollow' : 'follow'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('following');
                    this.classList.toggle('btn-primary');
                    this.classList.toggle('btn-outline-primary');
                    this.innerHTML = isFollowing ? 
                        '<i class="fas fa-user-plus me-1"></i>Follow' : 
                        '<i class="fas fa-user-check me-1"></i>Following';
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    
    // Handle like/unlike buttons
    const likeButtons = document.querySelectorAll('.like-btn');
    likeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const artworkId = this.dataset.artworkId;
            const isLiked = this.classList.contains('liked');
            
            fetch('/ajax/like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    artwork_id: artworkId,
                    action: isLiked ? 'unlike' : 'like'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.classList.toggle('liked');
                    const icon = this.querySelector('i');
                    icon.classList.toggle('fas');
                    icon.classList.toggle('far');
                    
                    const counter = this.querySelector('.like-count');
                    if (counter) {
                        counter.textContent = data.likes;
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
    
    // Handle comment form submission
    const commentForms = document.querySelectorAll('.comment-form');
    commentForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const artworkId = this.dataset.artworkId;
            const textarea = this.querySelector('textarea');
            const comment = textarea.value.trim();
            
            if (!comment) return;
            
            fetch('/ajax/comment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    artwork_id: artworkId,
                    comment: comment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add new comment to the list
                    const commentsList = document.querySelector(`#comments-${artworkId}`);
                    if (commentsList) {
                        const newComment = document.createElement('div');
                        newComment.className = 'comment mb-3';
                        newComment.innerHTML = `
                            <div class="d-flex">
                                <img src="${data.user_avatar}" class="avatar me-2" alt="${data.username}">
                                <div>
                                    <h6 class="mb-1">${data.username}</h6>
                                    <p class="mb-1">${data.comment}</p>
                                    <small class="text-muted">Just now</small>
                                </div>
                            </div>
                        `;
                        commentsList.insertBefore(newComment, commentsList.firstChild);
                    }
                    
                    // Clear the textarea
                    textarea.value = '';
                    
                    // Update comment count
                    const counter = document.querySelector(`#comment-count-${artworkId}`);
                    if (counter) {
                        counter.textContent = parseInt(counter.textContent) + 1;
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });
}); 