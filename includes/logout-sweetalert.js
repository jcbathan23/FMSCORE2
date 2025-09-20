// Universal Logout SweetAlert for SLATE System
// This script provides a consistent logout confirmation across all modules

function confirmLogout() {
  injectSwalFadeStylesOnce();
  Swal.fire({
    title: 'Confirm Logout',
    text: 'Are you sure you want to log out of SLATE system?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#667eea',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-box-arrow-right me-2"></i> Yes, Log Out',
    cancelButtonText: '<i class="bi bi-x-circle me-2"></i> Cancel',
    reverseButtons: true,
    showClass: { 
      popup: 'swal2-animate-show',
      backdrop: 'swal2-backdrop-show'
    },
    hideClass: { 
      popup: 'swal2-animate-hide',
      backdrop: 'swal2-backdrop-hide'
    },
    customClass: {
      popup: 'slate-logout-modal',
      confirmButton: 'slate-logout-confirm-btn',
      cancelButton: 'slate-logout-cancel-btn'
    },
    didOpen: () => {
      // Add fade animation styles
      const popup = document.querySelector('.swal2-popup');
      if (popup) {
        popup.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
      }
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // Show loading state before redirect
      Swal.fire({
        title: 'Logging out...',
        text: 'Please wait while we log you out',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        timer: 1500,
        timerProgressBar: true,
        showClass: { 
          popup: 'swal2-animate-show',
          backdrop: 'swal2-backdrop-show'
        },
        hideClass: { 
          popup: 'swal2-animate-hide',
          backdrop: 'swal2-backdrop-hide'
        }
      }).then(() => {
        window.location.href = 'login.php?logged_out=1';
      });
    } else if (result.isDismissed) {
      // Handle cancel button click with smooth fade
      const popup = document.querySelector('.swal2-popup');
      if (popup) {
        popup.style.transform = 'scale(0.95)';
        popup.style.opacity = '0';
        popup.style.transition = 'all 0.3s cubic-bezier(0.4, 0, 0.2, 1)';
      }
      
      // Fade out backdrop
      const backdrop = document.querySelector('.swal2-backdrop');
      if (backdrop) {
        backdrop.style.opacity = '0';
        backdrop.style.transition = 'opacity 0.3s ease-out';
      }
      
      // Close modal after fade animation
      setTimeout(() => {
        Swal.close();
      }, 300);
    }
  });
}

// Alternative quick logout function without confirmation (for emergency use)
function quickLogout() {
  injectSwalFadeStylesOnce();
  Swal.fire({
    title: 'Quick Logout',
    text: 'Logging you out immediately...',
    icon: 'info',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    timer: 1500,
    timerProgressBar: true,
    customClass: {
      popup: 'slate-quick-logout-modal'
    },
    showClass: { 
      popup: 'swal2-animate-show',
      backdrop: 'swal2-backdrop-show'
    },
    hideClass: { 
      popup: 'swal2-animate-hide',
      backdrop: 'swal2-backdrop-hide'
    },
    didOpen: () => {
      Swal.showLoading();
    }
  }).then(() => {
    window.location.href = 'login.php?quick_logout=1';
  });
}

// Session timeout warning
function showSessionWarning(minutesLeft = 5) {
  injectSwalFadeStylesOnce();
  Swal.fire({
    title: 'Session Expiring Soon',
    html: `Your session will expire in <b>${minutesLeft}</b> minutes.<br>Would you like to extend your session?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#f59e0b',
    cancelButtonColor: '#dc2626',
    confirmButtonText: '<i class="bi bi-arrow-clockwise me-2"></i>Extend Session',
    cancelButtonText: '<i class="bi bi-box-arrow-right me-2"></i>Logout Now',
    reverseButtons: false,
    allowOutsideClick: false,
    customClass: {
      popup: 'slate-warning-modal',
      confirmButton: 'slate-warning-confirm-btn',
      cancelButton: 'slate-warning-cancel-btn'
    },
    showClass: { 
      popup: 'swal2-animate-show',
      backdrop: 'swal2-backdrop-show'
    },
    hideClass: { 
      popup: 'swal2-animate-hide',
      backdrop: 'swal2-backdrop-hide'
    },
    didOpen: () => {
      // Add warning styling
      document.querySelector('.slate-warning-modal').style.borderRadius = '15px';
      document.querySelector('.slate-warning-modal').style.boxShadow = '0 10px 40px rgba(245, 158, 11, 0.2)';
    }
  }).then((result) => {
    if (result.isConfirmed) {
      // Extend session by making an AJAX request
      fetch('auth.php?extend_session=1')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              title: 'Session Extended!',
              text: 'Your session has been extended successfully.',
              icon: 'success',
              timer: 2000,
              timerProgressBar: true,
              showConfirmButton: false,
              customClass: {
                popup: 'slate-success-modal'
              },
              showClass: { 
                popup: 'swal2-animate-show',
                backdrop: 'swal2-backdrop-show'
              },
              hideClass: { 
                popup: 'swal2-animate-hide',
                backdrop: 'swal2-backdrop-hide'
              }
            });
          } else {
            confirmLogout();
          }
        })
        .catch(() => {
          confirmLogout();
        });
    } else if (result.isDismissed) {
      confirmLogout();
    }
  });
}

// Network error logout
function networkErrorLogout(message = 'Network connection lost') {
  injectSwalFadeStylesOnce();
  Swal.fire({
    title: 'Connection Error',
    text: `${message}. You will be logged out for security.`,
    icon: 'error',
    confirmButtonColor: '#dc2626',
    confirmButtonText: '<i class="bi bi-wifi-off me-2"></i>Logout',
    allowOutsideClick: false,
    allowEscapeKey: false,
    customClass: {
      popup: 'slate-error-modal',
      confirmButton: 'slate-error-btn'
    },
    showClass: { 
      popup: 'swal2-animate-show',
      backdrop: 'swal2-backdrop-show'
    },
    hideClass: { 
      popup: 'swal2-animate-hide',
      backdrop: 'swal2-backdrop-hide'
    }
  }).then(() => {
    window.location.href = 'login.php?network_error=1';
  });
}

// Function to inject fade styles once
function injectSwalFadeStylesOnce() {
  if (document.getElementById('slate-swal-fade-styles')) return;
  
  const fadeStyles = document.createElement('style');
  fadeStyles.id = 'slate-swal-fade-styles';
  fadeStyles.textContent = `
    /* Enhanced SweetAlert Fade Animations */
    .swal2-animate-show {
      animation: swal2-fadeIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .swal2-animate-hide {
      animation: swal2-fadeOut 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    
    .swal2-backdrop-show {
      animation: swal2-backdropFadeIn 0.3s ease-out !important;
    }
    
    .swal2-backdrop-hide {
      animation: swal2-backdropFadeOut 0.3s ease-out !important;
    }
    
    @keyframes swal2-fadeIn {
      from {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }
    
    @keyframes swal2-fadeOut {
      from {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
      to {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
      }
    }
    
    @keyframes swal2-backdropFadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes swal2-backdropFadeOut {
      from { opacity: 1; }
      to { opacity: 0; }
    }
    
    /* Enhanced button hover effects */
    .slate-logout-confirm-btn,
    .slate-logout-cancel-btn {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
      position: relative !important;
      overflow: hidden !important;
    }
    
    .slate-logout-confirm-btn::before,
    .slate-logout-cancel-btn::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 50%;
      transition: all 0.3s ease;
      transform: translate(-50%, -50%);
      z-index: 0;
    }
    
    .slate-logout-confirm-btn:hover::before,
    .slate-logout-cancel-btn:hover::before {
      width: 300px;
      height: 300px;
    }
    
    .slate-logout-confirm-btn:hover,
    .slate-logout-cancel-btn:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
    }
    
    .slate-logout-confirm-btn span,
    .slate-logout-cancel-btn span {
      position: relative;
      z-index: 1;
    }
  `;
  document.head.appendChild(fadeStyles);
}

// Custom CSS for SweetAlert modals
const slateAlertStyles = `
  <style>
    /* Custom SweetAlert Styling for SLATE */
    .slate-logout-modal,
    .slate-loading-modal,
    .slate-success-modal,
    .slate-warning-modal,
    .slate-error-modal,
    .slate-quick-logout-modal {
      font-family: 'Inter', 'Segoe UI', system-ui, sans-serif !important;
      border-radius: 16px !important;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
    }
    
    .slate-logout-title,
    .swal2-title {
      font-weight: 700 !important;
      font-size: 1.5rem !important;
      color: #1f2937 !important;
      margin-bottom: 1rem !important;
    }
    
    .slate-logout-content,
    .swal2-html-container {
      font-size: 1rem !important;
      color: #6b7280 !important;
      line-height: 1.6 !important;
    }
    
    .slate-logout-confirm-btn {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
      border: none !important;
      border-radius: 12px !important;
      padding: 0.875rem 2rem !important;
      font-weight: 600 !important;
      font-size: 1rem !important;
      color: white !important;
      position: relative !important;
      overflow: hidden !important;
    }
    
    .slate-logout-cancel-btn {
      background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%) !important;
      border: none !important;
      border-radius: 12px !important;
      padding: 0.875rem 2rem !important;
      font-weight: 600 !important;
      font-size: 1rem !important;
      color: white !important;
      position: relative !important;
      overflow: hidden !important;
    }
    
    .slate-success-btn {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 0.75rem 1.5rem !important;
      font-weight: 500 !important;
    }
    
    .slate-warning-confirm-btn {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 0.75rem 1.5rem !important;
      font-weight: 500 !important;
    }
    
    .slate-warning-cancel-btn {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 0.75rem 1.5rem !important;
      font-weight: 500 !important;
    }
    
    .slate-error-btn {
      background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%) !important;
      border: none !important;
      border-radius: 8px !important;
      padding: 0.75rem 1.5rem !important;
      font-weight: 500 !important;
    }
    
    /* Dark mode support */
    .dark-mode .slate-logout-title,
    .dark-mode .swal2-title {
      color: #f9fafb !important;
    }
    
    .dark-mode .slate-logout-content,
    .dark-mode .swal2-html-container {
      color: #d1d5db !important;
    }
    
    .dark-mode .slate-logout-modal,
    .dark-mode .slate-loading-modal,
    .dark-mode .slate-success-modal,
    .dark-mode .slate-warning-modal,
    .dark-mode .slate-error-modal,
    .dark-mode .slate-quick-logout-modal {
      background: #1f2937 !important;
      color: #f9fafb !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    
    /* Enhanced animations */
    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    
    .swal2-popup {
      animation: swal2-show 0.3s ease-out !important;
    }
    
    /* Icon styling */
    .swal2-icon.swal2-question {
      border-color: #667eea !important;
      color: #667eea !important;
    }
    
    .swal2-icon.swal2-question .swal2-icon-content {
      color: #667eea !important;
    }
  </style>
`;

// Add styles to document
if (typeof document !== 'undefined') {
  document.head.insertAdjacentHTML('beforeend', slateAlertStyles);
}

// Initialize session timeout checker (optional - can be enabled per module)
function initSessionTimeout(warningMinutes = 5, totalMinutes = 30) {
  const warningTime = (totalMinutes - warningMinutes) * 60 * 1000;
  const totalTime = totalMinutes * 60 * 1000;
  
  // Set warning timer
  setTimeout(() => {
    showSessionWarning(warningMinutes);
  }, warningTime);
  
  // Set automatic logout timer
  setTimeout(() => {
    quickLogout();
  }, totalTime);
}

// Export functions for use in modules
if (typeof window !== 'undefined') {
  window.confirmLogout = confirmLogout;
  window.quickLogout = quickLogout;
  window.showSessionWarning = showSessionWarning;
  window.networkErrorLogout = networkErrorLogout;
  window.initSessionTimeout = initSessionTimeout;
}
