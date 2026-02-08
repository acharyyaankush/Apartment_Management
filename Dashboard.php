<?php
require_once 'functions/functions.php';
checkAuth();

// Get current year and month data
$current_year = date('Y');
$current_month_full = date('F Y');
$current_month_expenses = getCurrentMonthExpenses();
$total_funds = getTotalFunds();
$total_maintenance = getTotalMaintenanceCollection();
$current_month_maintenance = getCurrentMonthMaintenanceCollection();
$current_month_funds = getCurrentMonthFunds();
$total_members = count(getMembers());

// Get monthly summary for the table (current year only)
$monthly_summary = getMonthlySummary(6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .current-badge {
            background-color: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8rem;
            margin-left: 5px;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-profit {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-loss {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-neutral {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }

        .expense-cell {
            color: #dc3545;
            font-weight: 500;
        }

        .maintenance-cell {
            color: #28a745;
            font-weight: 500;
        }

        .funds-cell {
            font-weight: 600;
        }

        .expense-total {
            color: #dc3545;
        }

        .maintenance-total {
            color: #28a745;
        }

        .funds-total {
            color: #007bff;
        }

        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .table th {
            background-color: #f8f9fa;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .table tfoot {
            background-color: #f8f9fa;
        }

        .table tfoot td {
            font-weight: 600;
            border-top: 2px solid #dee2e6;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'navbar.php'; ?>

<div class="container">
    <!-- Welcome Card -->
    <div class="card">
        <h2>Welcome to CPC Apartment Management System</h2>
        <p>Hello, <?php echo $_SESSION['admin_name']; ?>! Here's your dashboard overview.</p>
        <p style="color: #666; font-size: 0.9rem; margin-top: 10px;">
            Current Month: <?php echo $current_month_full; ?> | Year: <?php echo $current_year; ?>
        </p>
    </div>
    
    <!-- Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <!-- Current Month Expenses -->
        <div class="card">
            <h3>Current Month Expenses</h3>
            <p style="font-size: 2rem; color: #dc3545; margin: 1rem 0;">
                ₹<?php echo number_format($current_month_expenses, 2); ?>
            </p>
            <small style="color: #666;">Expenses for <?php echo date('F'); ?> <?php echo $current_year; ?></small>
        </div>
        
        <!-- Total Funds -->
        <div class="card">
            <h3>Total Funds</h3>
            <p style="font-size: 2rem; color: #28a745; margin: 1rem 0;">
                ₹<?php echo number_format($total_funds, 2); ?>
            </p>
            <small style="color: #666;">Total: (All Maintenance - All Expenses)</small>
        </div>
        
        <!-- Total Maintenance Collection -->
        <div class="card">
            <h3>Total Maintenance Collection</h3>
            <p style="font-size: 2rem; color: #28a745; margin: 1rem 0;">
                ₹<?php echo number_format($total_maintenance, 2); ?>
            </p>
            <small style="color: #666;">All-time maintenance collection</small>
        </div>
        
        <!-- Total Members -->
        <div class="card">
            <h3>Total Members</h3>
            <p style="font-size: 2rem; color: #6c757d; margin: 1rem 0;">
                <?php echo $total_members; ?>
            </p>
            <small style="color: #666;">Registered apartment members</small>
        </div>
    </div>
    
    <!-- Monthly History Table -->
    <div class="card">
        <div class="header-row">
            <div>
                <h3 style="margin: 0;"><?php echo $current_year; ?> Monthly Financial History</h3>
                <p style="color: #666; margin: 5px 0 0 0;">Showing data for <?php echo $current_year; ?></p>
            </div>
            <button class="" onclick="downloadMonthlyPDF()" style="width:18%;padding:13px;background-color:red;border:white;color:white;">
                <i class="fas fa-file-pdf" style="color:white;"></i> Download PDF Report
            </button>
        </div>
        
        <?php if (empty($monthly_summary)): ?>
            <div class="alert alert-error">No financial data available for <?php echo $current_year; ?>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table" id="monthlyHistoryTable">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Month (<?php echo $current_year; ?>)</th>
                            <th>Expenses (₹)</th>
                            <th>Maintenance (₹)</th>
                            <th>Funds (₹)</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $serial = 1;
                        $total_all_expenses = 0;
                        $total_all_maintenance = 0;
                        $total_all_funds = 0;
                        
                        foreach ($monthly_summary as $summary): 
                            $month_year = $summary['month_year'];
                            $month = substr($month_year, 0, 2);
                            $year = substr($month_year, 3);
                            $display_month = date('F', mktime(0, 0, 0, $month, 1, 2000));
                            
                            $total_expenses = $summary['total_expenses'] ?? 0;
                            $total_maintenance_month = $summary['total_maintenance'] ?? 0;
                            $month_funds = $summary['month_funds'] ?? 0;
                            
                            $total_all_expenses += $total_expenses;
                            $total_all_maintenance += $total_maintenance_month;
                            $total_all_funds += $month_funds;
                            
                            // Determine status
                            if ($month_funds > 0) {
                                $status = 'Profit';
                                $status_class = 'status-profit';
                            } elseif ($month_funds < 0) {
                                $status = 'Loss';
                                $status_class = 'status-loss';
                            } else {
                                $status = 'Break Even';
                                $status_class = 'status-neutral';
                            }
                        ?>
                        <tr>
                            <td><?php echo $serial++; ?></td>
                            <td>
                                <?php echo $display_month; ?>
                                <?php if ($month_year == date('m-Y')): ?>
                                    <span class="current-badge">Current</span>
                                <?php endif; ?>
                            </td>
                            <td class="expense-cell">
                                ₹<?php echo number_format($total_expenses, 2); ?>
                            </td>
                            <td class="maintenance-cell">
                                ₹<?php echo number_format($total_maintenance_month, 2); ?>
                            </td>
                            <td class="funds-cell">
                                ₹<?php echo number_format($month_funds, 2); ?>
                            </td>
                            <td>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot style="background-color: #f8f9fa;">
                        <tr>
                            <td colspan="2"><strong>Total</strong></td>
                            <td><strong class="expense-total">₹<?php echo number_format($total_all_expenses, 2); ?></strong></td>
                            <td><strong class="maintenance-total">₹<?php echo number_format($total_all_maintenance, 2); ?></strong></td>
                            <td><strong class="funds-total">₹<?php echo number_format($total_all_funds, 2); ?></strong></td>
                            <td>
                                <?php if ($total_all_funds > 0): ?>
                                    <span class="status-badge status-profit">Overall Profit</span>
                                <?php elseif ($total_all_funds < 0): ?>
                                    <span class="status-badge status-loss">Overall Loss</span>
                                <?php else: ?>
                                    <span class="status-badge status-neutral">Break Even</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function downloadMonthlyPDF() {
        const btn = event.target;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        btn.disabled = true;
        
        // Create a temporary div to hold the PDF content
        const tempDiv = document.createElement('div');
        tempDiv.id = 'pdf-content';
        tempDiv.style.position = 'absolute';
        tempDiv.style.left = '-9999px';
        tempDiv.style.top = '0';
        tempDiv.style.width = '800px';
        tempDiv.style.padding = '20px';
        tempDiv.style.backgroundColor = '#ffffff';
        
        // Build the PDF content with header and table
        const currentYear = <?php echo $current_year; ?>;
        const currentDate = '<?php echo date("F j, Y"); ?>';
        
        tempDiv.innerHTML = `
            <div style="text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 15px;">
                <h1 style="color: #2c3e50; margin: 0 0 10px 0; font-size: 24px;">CPC Apartment Management System</h1>
                <h2 style="color: #7f8c8d; margin: 0 0 5px 0; font-size: 18px; font-weight: normal;">Monthly Financial Report</h2>
                <h3 style="color: #34495e; margin: 0 0 10px 0; font-size: 16px;">Year: ${currentYear}</h3>
                <div style="color: #95a5a6; font-size: 12px;">Generated on: ${currentDate}</div>
            </div>
            
            
            <div id="table-for-pdf">
                ${document.getElementById('monthlyHistoryTable').outerHTML}
            </div>
            
            
            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd; color: #7f8c8d; font-size: 10px; text-align: center;">
                <p>Generated by CPC Apartment Management System | For official use only</p>
            </div>
        `;
        
        // Style the table for PDF
        const table = tempDiv.querySelector('#table-for-pdf table');
        if (table) {
            table.style.width = '100%';
            table.style.borderCollapse = 'collapse';
            table.style.fontSize = '12px';
            
            // Style table headers
            const thElements = table.querySelectorAll('th');
            thElements.forEach(th => {
                th.style.backgroundColor = '#2c3e50';
                th.style.color = 'white';
                th.style.padding = '8px';
                th.style.border = '1px solid #34495e';
                th.style.textAlign = 'left';
            });
            
            // Style table cells
            const tdElements = table.querySelectorAll('td');
            tdElements.forEach(td => {
                td.style.padding = '6px';
                td.style.border = '1px solid #ddd';
            });
            
            // Style table rows
            const trElements = table.querySelectorAll('tr');
            trElements.forEach((tr, index) => {
                if (index % 2 === 0) {
                    tr.style.backgroundColor = '#f8f9fa';
                }
            });
            
            // Style total row if exists
            const tfoot = table.querySelector('tfoot');
            if (tfoot) {
                tfoot.style.backgroundColor = '#34495e';
                tfoot.style.color = 'white';
                tfoot.style.fontWeight = 'bold';
            }
        }
        
        document.body.appendChild(tempDiv);
        
        // Capture the entire PDF content
        html2canvas(tempDiv, {
            scale: 2,
            useCORS: true,
            logging: false,
            backgroundColor: '#ffffff'
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jspdf.jsPDF('p', 'mm', 'a4');
            
            const imgWidth = 190;
            const pageHeight = 280;
            const imgHeight = canvas.height * imgWidth / canvas.width;
            let heightLeft = imgHeight;
            let position = 10;
            
            pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
            
            while (heightLeft >= 0) {
                position = heightLeft - imgHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            
            pdf.save(`CPC_Apartment_Monthly_Report_${currentYear}_${<?php echo date('Y-m-d'); ?>}.pdf`);
            
            // Clean up
            document.body.removeChild(tempDiv);
            
            // Reset button
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        }).catch(error => {
            console.error('Error:', error);
            
            // Clean up even on error
            if (tempDiv.parentNode) {
                document.body.removeChild(tempDiv);
            }
            
            alert('Error generating PDF. Trying alternative method...');
            
            // Fallback to HTML download
            downloadHTMLReport();
            
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
    }

    // Helper functions to calculate totals from the table
    function getTotalExpenses() {
        const table = document.getElementById('monthlyHistoryTable');
        const tfoot = table.querySelector('tfoot');
        if (tfoot) {
            const cells = tfoot.querySelectorAll('td');
            if (cells.length >= 3) {
                return cells[2].textContent.replace('₹', '').trim();
            }
        }
        return '0.00';
    }

    function getTotalMaintenance() {
        const table = document.getElementById('monthlyHistoryTable');
        const tfoot = table.querySelector('tfoot');
        if (tfoot) {
            const cells = tfoot.querySelectorAll('td');
            if (cells.length >= 4) {
                return cells[3].textContent.replace('₹', '').trim();
            }
        }
        return '0.00';
    }

    function getNetFunds() {
        const table = document.getElementById('monthlyHistoryTable');
        const tfoot = table.querySelector('tfoot');
        if (tfoot) {
            const cells = tfoot.querySelectorAll('td');
            if (cells.length >= 5) {
                return cells[4].textContent.replace('₹', '').trim();
            }
        }
        return '0.00';
    }

    function getNetFundsColor() {
        const netFunds = getNetFunds();
        return netFunds.startsWith('-') ? '#e74c3c' : '#27ae60';
    }

    function getNetFundsStatus() {
        const netFunds = parseFloat(getNetFunds().replace(/,/g, ''));
        return netFunds > 0 ? 'Profit' : netFunds < 0 ? 'Loss' : 'Break Even';
    }

    function downloadHTMLReport() {
        const formData = new FormData();
        formData.append('download_monthly_pdf', 'true');
        formData.append('year', '<?php echo $current_year; ?>');
        
        fetch('functions/download_monthly_pdf.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.blob())
        .then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'CPC_Apartment_Monthly_Report_<?php echo $current_year; ?>.html';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error downloading report. Please try again.');
        });
    }
</script>
<?php include 'footer.php'; ?>
</body>
</html>