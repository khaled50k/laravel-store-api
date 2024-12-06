import './bootstrap';
window.Echo.channel('admin-notifications')
    .listen('NewUserRegistered', (event) => {
        console.log('New User Registered:', event);
        alert(`New User Registered: ${event.name} (${event.email})`);
    });
