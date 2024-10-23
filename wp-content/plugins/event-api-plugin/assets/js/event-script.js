document.addEventListener('DOMContentLoaded', function() {
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const eventsWrapper = document.querySelector('.events-wrapper');

    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            eventsWrapper.className = `events-wrapper ${this.dataset.view}-view`;
        });
    });

    // Handle card clicks
    const eventCards = document.querySelectorAll('.event-card-link');
    eventCards.forEach(card => {
        card.addEventListener('click', function(e) {
            e.preventDefault();
            // Add your logic here to handle the card click
            console.log('Card clicked:', this.querySelector('.event-title').textContent);
        });
    });

    // Handle button clicks
    const viewButtons = document.querySelectorAll('.view-event-btn');
    viewButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation(); // Prevent the card click event from firing
            // Add your logic here to handle the button click
            console.log('Button clicked:', this.closest('.event-card').querySelector('.event-title').textContent);
        });
    });
});