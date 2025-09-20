/**
 * Common logout functionality for all CORE II modules
 * Requires SweetAlert2 to be loaded
 */

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
    
    /* Custom SweetAlert Styling for CORE II */
    .slate-logout-modal {
      font-family: 'Inter', 'Segoe UI', system-ui, sans-serif !important;
      border-radius: 16px !important;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15) !important;
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
    
    /* Dark mode support */
    .dark-mode .slate-logout-modal {
      background: #1f2937 !important;
      color: #f9fafb !important;
      border: 1px solid rgba(255, 255, 255, 0.1) !important;
    }
    
    .dark-mode .swal2-title {
      color: #f9fafb !important;
    }
    
    .dark-mode .swal2-html-container {
      color: #d1d5db !important;
    }
  `;
  document.head.appendChild(fadeStyles);
}

function confirmLogout() {
  injectSwalFadeStylesOnce();
  Swal.fire({
    title: 'Confirm Logout',
    text: 'Are you sure you want to log out of CORE II System?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#667eea',
    cancelButtonColor: '#6c757d',
    confirmButtonText: '<i class="bi bi-box-arrow-right me-2"></i> Logout',
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
        window.location.href = 'auth.php?logout=1';
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

/**
 * Initialize logout functionality for sidebar logout links
 */
function initializeLogout() {
  // Find all logout links and add click handlers
  const logoutLinks = document.querySelectorAll('a[href*="logout"], a[onclick*="logout"]');
  
  logoutLinks.forEach(link => {
    // Remove existing onclick handlers
    link.removeAttribute('onclick');
    
    // Remove href to prevent default navigation
    if (link.href && link.href.includes('logout')) {
      link.href = '#';
    }
    
    // Add new click handler
    link.addEventListener('click', function(e) {
      e.preventDefault();
      confirmLogout();
    });
  });
  
  // Also handle any existing confirmLogout calls
  window.confirmLogout = confirmLogout;
}

// Auto-initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
  // Small delay to ensure other scripts have loaded
  setTimeout(() => {
    initializeLogout();
  }, 100);
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { confirmLogout, initializeLogout };
}
