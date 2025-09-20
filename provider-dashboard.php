<?php
require_once 'auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Allow access for providers and admins (admins can access provider dashboard)
if (!isProvider() && !isAdmin()) {
    header('Location: login.php?error=access_denied');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Provider Dashboard | CORE II</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
    body {
      background-color: #f8f9fa;
    }
    .dashboard-card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      transition: all 0.15s ease-in-out;
    }
    .dashboard-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    .stat-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
    }
    .sidebar {
      background: linear-gradient(180deg, #2c3e50 0%, #34495e 100%);
      min-height: 100vh;
    }
    .nav-link {
      color: rgba(255, 255, 255, 0.8);
      border-radius: 8px;
      margin: 2px 0;
    }
    .nav-link:hover, .nav-link.active {
      background-color: rgba(255, 255, 255, 0.1);
      color: white;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="position-sticky pt-3">
          <div class="text-center mb-4">
            <img src="slatelogo.png" alt="SLATE Logo" class="img-fluid" style="max-width: 80px;">
            <h5 class="text-white mt-2">Provider Portal</h5>
            <small class="text-light">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></small>
          </div>
          
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link active" href="#dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="schedules.php">
                <i class="bi bi-calendar-check"></i> Schedules
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="service-network.php">
                <i class="bi bi-diagram-3"></i> Service Network
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="service-provider.php">
                <i class="bi bi-truck"></i> Services
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="setup_2fa.php">
                <i class="bi bi-shield-check"></i> Security
              </a>
            </li>
            <li class="nav-item mt-3">
              <a class="nav-link text-danger" href="login.php?logout=1">
                <i class="bi bi-box-arrow-right"></i> Logout
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <!-- Main content -->
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
          <h1 class="h2">Provider Dashboard</h1>
          <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
              <button type="button" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-download"></i> Export
              </button>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card stat-card">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Active Services</div>
                    <div class="h5 mb-0 font-weight-bold">12</div>
                  </div>
                  <div class="col-auto">
                    <i class="bi bi-truck text-white-50" style="font-size: 2rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Pending Requests</div>
                    <div class="h5 mb-0 font-weight-bold">8</div>
                  </div>
                  <div class="col-auto">
                    <i class="bi bi-clock text-white-50" style="font-size: 2rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Monthly Revenue</div>
                    <div class="h5 mb-0 font-weight-bold">₱85,420</div>
                  </div>
                  <div class="col-auto">
                    <i class="bi bi-currency-dollar text-white-50" style="font-size: 2rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-3 col-md-6 mb-4">
            <div class="card dashboard-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
              <div class="card-body">
                <div class="row no-gutters align-items-center">
                  <div class="col mr-2">
                    <div class="text-xs font-weight-bold text-uppercase mb-1">Service Rating</div>
                    <div class="h5 mb-0 font-weight-bold">4.8/5</div>
                  </div>
                  <div class="col-auto">
                    <i class="bi bi-star-fill text-white-50" style="font-size: 2rem;"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="row">
          <div class="col-lg-8">
            <div class="card dashboard-card">
              <div class="card-header">
                <h5 class="mb-0">Recent Service Requests</h5>
              </div>
              <div class="card-body">
                <div class="table-responsive">
                  <table class="table table-hover">
                    <thead>
                      <tr>
                        <th>Request ID</th>
                        <th>Service Type</th>
                        <th>Route</th>
                        <th>Status</th>
                        <th>Date</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>#SR001</td>
                        <td>Freight Transport</td>
                        <td>Manila → Cebu</td>
                        <td><span class="badge bg-success">Completed</span></td>
                        <td>Dec 15, 2024</td>
                      </tr>
                      <tr>
                        <td>#SR002</td>
                        <td>Express Delivery</td>
                        <td>Davao → Manila</td>
                        <td><span class="badge bg-warning">In Transit</span></td>
                        <td>Dec 16, 2024</td>
                      </tr>
                      <tr>
                        <td>#SR003</td>
                        <td>Cargo Transport</td>
                        <td>Baguio → Iloilo</td>
                        <td><span class="badge bg-primary">Pending</span></td>
                        <td>Dec 17, 2024</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card dashboard-card">
              <div class="card-header">
                <h5 class="mb-0">Service Performance</h5>
              </div>
              <div class="card-body">
                <canvas id="performanceChart" width="400" height="200"></canvas>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Completed', 'In Transit', 'Pending'],
        datasets: [{
          data: [65, 25, 10],
          backgroundColor: [
            '#28a745',
            '#ffc107',
            '#007bff'
          ],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
  </script>
</body>
</html>
