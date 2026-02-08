<?php
require_once __DIR__ . '/../functions/functions.php';
checkAuth();

$success = '';
$error = '';

// Handle Add Expense
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_expense'])) {
    $title = $_POST['title'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $date = $_POST['expense_date'];
    
    if (addExpense($title, $amount, $description, $date)) {
        $success = "Expense added successfully!";
    } else {
        $error = "Failed to add expense!";
    }
}

// Get expenses for the selected month (for AJAX)
$expenses = getExpenses();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style.css">
    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <style>
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .pdf-download-section {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }
        
        .month-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 0.9rem;
        }
        
        .btn-pdf {
            background-color: #dc3545;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: background-color 0.3s;
        }
        
        .btn-pdf:hover {
            background-color: #c82333;
        }
        
        .total-amount {
            font-size: 1.2rem;
            font-weight: bold;
            color: #28a745;
            margin: 1rem 0;
            padding: 0.5rem;
            background-color: #f8f9fa;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        
        .loading {
            display: none;
            margin-left: 10px;
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
<?php include '../navbar.php'; ?>

<div class="container">
    <div class="card">
        <!-- Header with Title and PDF Download -->
        <div class="header-row">
            <h3>Expenses Management</h3>
            <div class="pdf-download-section">
                <select id="pdfMonth" class="month-select">
                    <?php
                    // Generate month options for the last 12 months
                    for ($i = 0; $i < 12; $i++) {
                        $month = date('Y-m', strtotime("-$i months"));
                        $display = date('F Y', strtotime($month . '-01'));
                        $selected = $i == 0 ? 'selected' : '';
                        echo "<option value='$month' $selected>$display</option>";
                    }
                    ?>
                </select>
                <button onclick="downloadExpensesPDF()" class="btn-pdf" id="pdfBtn">
                    📄 Download PDF
                </button>
                <span class="loading" id="pdfLoading">Generating PDF...</span>
            </div>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <!-- Total Expenses -->
        <!-- <?php
        $total_expenses = 0;
        foreach ($expenses as $expense) {
            $total_expenses += $expense['amount'];
        }
        ?>
        <div class="total-amount">
            Total Expenses: $<?php echo number_format($total_expenses, 2); ?>
        </div> -->
        
        <!-- Add Expense Form -->
        <div class="form-container">
            <h4>Add New Expense</h4>
            <form method="POST" action="">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label>Title:</label>
                        <input type="text" name="title" required>
                    </div>
                    <div class="form-group">
                        <label>Amount:</label>
                        <input type="number" name="amount" step="0.01" required>
                    </div>
                    <div class="form-group" style="grid-column: span 2;">
                        <label>Description:</label>
                        <textarea name="description" rows="3" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 5px;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Date:</label>
                        <input type="date" name="expense_date" required>
                    </div>
                </div>
                <button type="submit" name="add_expense" class="btn btn-primary" style="margin-top: 1rem;">Add Expense</button>
            </form>
        </div>
        
        <!-- Expenses List -->
        <h4 style="margin-top: 2rem;">All Expenses</h4>
        <?php if (empty($expenses)): ?>
            <div class="alert alert-error">No expenses found.</div>
        <?php else: ?>
            <table class="table" id="expensesTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo $expense['id']; ?></td>
                        <td><?php echo htmlspecialchars($expense['title']); ?></td>
                        <td>₹<?php echo number_format($expense['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($expense['description']); ?></td>
                        <td><?php echo date('M j, Y', strtotime($expense['expense_date'])); ?></td>
                        <td><?php echo date('M j, Y', strtotime($expense['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<script>
    // Function to download PDF using jsPDF - Matching Dashboard Style
    async function downloadExpensesPDF() {
        const selectedMonth = document.getElementById('pdfMonth').value;
        const monthName = document.getElementById('pdfMonth').selectedOptions[0].text;
        const pdfBtn = document.getElementById('pdfBtn');
        const pdfLoading = document.getElementById('pdfLoading');
        const errorDiv = document.getElementById('pdfError');
        
        // Clear previous errors
        if (errorDiv) errorDiv.textContent = '';
        
        // Show loading
        pdfBtn.disabled = true;
        pdfBtn.innerHTML = '⏳ Generating...';
        pdfLoading.style.display = 'inline';
        
        try {
            // 1. First check if jsPDF is loaded
            if (typeof window.jspdf === 'undefined') {
                throw new Error('PDF library not loaded. Please check internet connection.');
            }
            
            // 2. Fetch expenses for selected month
            const response = await fetch(`../ajax/get_expenses_by_month.php?month=${selectedMonth}`);
            
            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.message || 'Failed to load expenses');
            }
            
            // 3. Generate PDF with dashboard style
            await generateDashboardStylePDF(data.expenses, monthName, data.total);
            
        } catch (error) {
            console.error('PDF Generation Error:', error);
            
            // Show error message
            let errorMessage = error.message || 'Failed to generate PDF';
            
            // Try fallback method
            await tryFallbackPDF(selectedMonth, monthName);
            alert(`PDF generation failed: ${errorMessage}\nTrying fallback method...`);
            
        } finally {
            // Reset button
            resetPDFButton();
        }
    }
  // Generate PDF with Dashboard Design (NO SUMMARY BOX)
async function generateDashboardStylePDF(expenses, monthName, totalAmount) {
    return new Promise((resolve, reject) => {
        try {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Enable UTF-8 for Unicode characters (including ₹)
            doc.setLanguage('en-IN'); // Set to Indian English
            
            // Set document properties
            doc.setProperties({
                title: `CPC Apartment Expenses - ${monthName}`,
                subject: 'Expenses Report',
                author: 'CPC Apartment Management System',
                keywords: 'expenses, report, apartment, management',
                creator: 'CPC Apartment Management System'
            });
            
            // ===== HEADER SECTION =====
            const pageWidth = doc.internal.pageSize.getWidth();
            
            // Header background
            doc.setFillColor(44, 62, 80);
            doc.rect(0, 0, pageWidth, 40, 'F');
            
            // CPC Apartment Title
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(20);
            doc.setFont("helvetica", "bold");
            doc.text('CPC APARTMENT', pageWidth / 2, 15, { align: 'center' });
            
            // Report Title
            doc.setFontSize(16);
            doc.text('Expenses Management Report', pageWidth / 2, 25, { align: 'center' });
            
            // Month and Date
            doc.setFontSize(11);
            doc.text(`Month: ${monthName}`, 15, 35);
            doc.text(`Generated: ${new Date().toLocaleDateString()}`, pageWidth - 15, 35, { align: 'right' });
            
            // Report ID
            doc.text(`Report ID: EXP-${new Date().getTime()}`, pageWidth / 2, 35, { align: 'center' });
            
            // ===== EXPENSES TABLE =====
            const tableStartY = 50;
            
            // Table header
            doc.setFillColor(52, 73, 94);
            doc.rect(15, tableStartY, pageWidth - 30, 10, 'F');
            
            doc.setTextColor(255, 255, 255);
            doc.setFontSize(11);
            doc.setFont("helvetica", "bold");
            
            // Table columns - Use text() method with proper encoding
            const colWidths = [15, 45, 30, 30, 50];
            const colPositions = [20];
            for (let i = 1; i < colWidths.length; i++) {
                colPositions.push(colPositions[i-1] + colWidths[i-1]);
            }
            
            doc.text('ID', colPositions[0], tableStartY + 7);
            doc.text('Title', colPositions[1], tableStartY + 7);
            
            // For rupee symbol in header, we need special handling
            // Method 1: Use Unicode character directly
            doc.text('Amount ', colPositions[2], tableStartY + 7);
            
            doc.text('Date', colPositions[3], tableStartY + 7);
            doc.text('Description', colPositions[4], tableStartY + 7);
            
            // Table rows
            let currentY = tableStartY + 10;
            let rowHeight = 8;
            let rowColor = false;
            
            doc.setFont("helvetica", "normal");
            doc.setFontSize(10);
            
            if (expenses.length === 0) {
                doc.setFillColor(248, 249, 250);
                doc.rect(15, currentY, pageWidth - 30, rowHeight, 'F');
                
                doc.setTextColor(108, 117, 125);
                doc.text('No expenses found for this month', pageWidth / 2, currentY + 5, { align: 'center' });
                currentY += rowHeight;
            } else {
                expenses.forEach((expense, index) => {
                    // Alternating row colors
                    if (rowColor) {
                        doc.setFillColor(248, 249, 250);
                    } else {
                        doc.setFillColor(255, 255, 255);
                    }
                    doc.rect(15, currentY, pageWidth - 30, rowHeight, 'F');
                    
                    // Row border
                    doc.setDrawColor(221, 221, 221);
                    doc.setLineWidth(0.1);
                    doc.line(15, currentY + rowHeight, pageWidth - 15, currentY + rowHeight);
                    
                    // Expense data
                    doc.setTextColor(44, 62, 80);
                    doc.text(expense.id.toString(), colPositions[0], currentY + 5);
                    
                    const title = expense.title.length > 20 ? expense.title.substring(0, 20) + '...' : expense.title;
                    doc.text(title, colPositions[1], currentY + 5);
                    
                    // Amount with rupee symbol - USING UNICODE
                    doc.setTextColor(231, 76, 60);
                    
                    // Method 3: Alternative if rupee symbol doesn't display
                     const amountText = 'INR ' + parseFloat(expense.amount).toFixed(2);
                     doc.text(amountText, colPositions[2], currentY + 5);
                    
                    // Date
                    doc.setTextColor(44, 62, 80);
                    const date = new Date(expense.expense_date).toLocaleDateString('en-IN');
                    doc.text(date, colPositions[3], currentY + 5);
                    
                    // Description
                    if (expense.description) {
                        const desc = expense.description.length > 25 ? 
                            expense.description.substring(0, 25) + '...' : expense.description;
                        doc.text(desc, colPositions[4], currentY + 5);
                    }
                    
                    currentY += rowHeight;
                    rowColor = !rowColor;
                    
                    // Check if we need a new page
                    if (currentY > doc.internal.pageSize.getHeight() - 30) {
                        doc.addPage();
                        currentY = 20;
                        
                        doc.setFillColor(52, 73, 94);
                        doc.rect(15, currentY - 10, pageWidth - 30, 10, 'F');
                        doc.setTextColor(255, 255, 255);
                        doc.setFont("helvetica", "bold");
                        doc.text('ID', colPositions[0], currentY - 3);
                        doc.text('Title', colPositions[1], currentY - 3);
                        doc.text('Amount ', colPositions[2], currentY - 3);
                        doc.text('Date', colPositions[3], currentY - 3);
                        doc.text('Description', colPositions[4], currentY - 3);
                        
                        doc.setFont("helvetica", "normal");
                        currentY += rowHeight;
                    }
                });
            }
            
            // ===== FOOTER SECTION =====
            const footerY = doc.internal.pageSize.getHeight() - 20;
            
            doc.setDrawColor(221, 221, 221);
            doc.setLineWidth(0.5);
            doc.line(15, footerY - 15, pageWidth - 15, footerY - 15);
            
            doc.setTextColor(127, 140, 141);
            doc.setFontSize(9);
            doc.setFont("helvetica", "normal");
            
            doc.text('Generated by CPC Apartment Management System | For official use only', pageWidth / 2, footerY - 10, { align: 'center' });
            
            const pageCount = doc.internal.getNumberOfPages();
            for (let i = 1; i <= pageCount; i++) {
                doc.setPage(i);
                doc.text(`Page ${i} of ${pageCount}`, pageWidth - 15, footerY - 10, { align: 'right' });
            }
            
            // ===== SAVE PDF =====
            const fileName = `CPC_Apartment_Expenses_${monthName.replace(/[^a-zA-Z0-9]/g, '_')}_${new Date().getFullYear()}.pdf`;
            doc.save(fileName);
            
            resolve();
            
        } catch (error) {
            reject(error);
        }
    });
}

    // Fallback method with Dashboard Design
    async function tryFallbackPDF(selectedMonth, monthName) {
        try {
            const response = await fetch(`../ajax/get_expenses_by_month.php?month=${selectedMonth}`);
            const data = await response.json();
            
            if (!data.success) return;
            
            // Create printable content with Dashboard Style (NO SUMMARY BOX)
            let html = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>CPC Apartment - Expenses Report - ${monthName}</title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
                    
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    
                    body {
                        font-family: 'Inter', Arial, sans-serif;
                        margin: 0;
                        padding: 30px;
                        background: #f8f9fa;
                        color: #2c3e50;
                    }
                    
                    .pdf-container {
                        max-width: 1000px;
                        margin: 0 auto;
                        background: white;
                        border-radius: 10px;
                        box-shadow: 0 5px 30px rgba(0,0,0,0.1);
                        overflow: hidden;
                    }
                    
                    /* Header matching dashboard */
                    .pdf-header {
                        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
                        color: white;
                        padding: 30px 40px;
                        text-align: center;
                        border-bottom: 5px solid #3498db;
                    }
                    
                    .apartment-name {
                        font-size: 32px;
                        font-weight: 700;
                        letter-spacing: 1px;
                        margin-bottom: 5px;
                    }
                    
                    .report-title {
                        font-size: 20px;
                        font-weight: 400;
                        opacity: 0.9;
                        margin-bottom: 15px;
                    }
                    
                    .report-meta {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        font-size: 14px;
                        opacity: 0.8;
                        margin-top: 15px;
                        padding-top: 15px;
                        border-top: 1px solid rgba(255,255,255,0.2);
                    }
                    
                    /* Table matching dashboard */
                    .table-container {
                        margin: 30px;
                        overflow-x: auto;
                    }
                    
                    .expenses-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 14px;
                        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
                        border-radius: 8px;
                        overflow: hidden;
                    }
                    
                    .expenses-table thead {
                        background: #34495e;
                        color: white;
                    }
                    
                    .expenses-table th {
                        padding: 15px 12px;
                        text-align: left;
                        font-weight: 600;
                        font-size: 13px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                    
                    .expenses-table tbody tr {
                        border-bottom: 1px solid #f1f1f1;
                        transition: background 0.2s;
                    }
                    
                    .expenses-table tbody tr:nth-child(even) {
                        background: #f8f9fa;
                    }
                    
                    .expenses-table tbody tr:hover {
                        background: #ecf0f1;
                    }
                    
                    .expenses-table td {
                        padding: 14px 12px;
                        color: #2c3e50;
                    }
                    
                    .expenses-table td.amount {
                        color: #e74c3c;
                        font-weight: 600;
                    }
                    
                    /* Footer (WITHOUT CALCULATION LINE) */
                    .pdf-footer {
                        margin: 30px;
                        padding-top: 20px;
                        border-top: 1px solid #e0e0e0;
                        color: #7f8c8d;
                        font-size: 12px;
                        text-align: center;
                    }
                    
                    /* REMOVED: .footer-note class (calculation line removed) */
                    
                    /* Print styles */
                    @media print {
                        @page {
                            size: A4 portrait;
                            margin: 0.5in;
                        }
                        
                        body {
                            background: white;
                            padding: 0;
                            margin: 0;
                        }
                        
                        .pdf-container {
                            box-shadow: none;
                            border-radius: 0;
                            max-width: 100%;
                        }
                        
                        .no-print {
                            display: none !important;
                        }
                        
                        .pdf-header {
                            border: none;
                        }
                        
                        .expenses-table {
                            box-shadow: none;
                        }
                    }
                    
                    .no-print {
                        text-align: center;
                        padding: 30px;
                        background: #f8f9fa;
                    }
                    
                    .print-button {
                        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
                        color: white;
                        border: none;
                        padding: 15px 40px;
                        font-size: 16px;
                        font-weight: 600;
                        border-radius: 6px;
                        cursor: pointer;
                        display: inline-flex;
                        align-items: center;
                        gap: 10px;
                        transition: all 0.3s ease;
                        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
                    }
                    
                    .print-button:hover {
                        transform: translateY(-2px);
                        box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
                    }
                    
                    .instruction {
                        margin-top: 15px;
                        font-size: 13px;
                        color: #7f8c8d;
                    }
                    
                    .rupee-symbol {
                        font-family: Arial, sans-serif;
                    }
                </style>
            </head>
            <body>
                <div class="pdf-container">
                    <div class="pdf-header">
                        <div class="apartment-name">CPC APARTMENT</div>
                        <div class="report-title">Expenses Management Report</div>
                        <div class="report-meta">
                            <div><strong>Month:</strong> ${monthName}</div>
                            <div><strong>Report ID:</strong> EXP-${new Date().getTime()}</div>
                            <div><strong>Generated:</strong> ${new Date().toLocaleDateString()}</div>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="expenses-table">
                            <thead>
                                <tr>
                                    <th width="50">ID</th>
                                    <th>Title</th>
                                    <th width="120">Amount</th>
                                    <th width="100">Date</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>`;
            
            if (data.expenses.length > 0) {
                data.expenses.forEach(expense => {
                    html += `
                    <tr>
                        <td>${expense.id}</td>
                        <td>${expense.title}</td>
                        <td class="amount rupee-symbol">${parseFloat(expense.amount).toFixed(2)}</td>
                        <td>${new Date(expense.expense_date).toLocaleDateString('en-IN')}</td>
                        <td>${expense.description || '-'}</td>
                    </tr>`;
                });
            } else {
                html += `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 50px; color: #7f8c8d;">
                        <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
                        <h3 style="margin-bottom: 10px; color: #95a5a6;">No Expenses Found</h3>
                        <p>No expenses were recorded for ${monthName}</p>
                    </td>
                </tr>`;
            }
            
            html += `
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="pdf-footer">
                        <div>
                            <strong>Generated by CPC Apartment Management System | For official use only</strong>
                        </div>
                    </div>
                </div>
                
                <div class="no-print">
                    <div style="text-align: center; padding: 30px;">
                        <button class="print-button" onclick="window.print()">
                            🖨️ Print / Save as PDF
                        </button>
                        <div class="instruction">
                            Click the button above, then select "Save as PDF" in the print dialog<br>
                            <small>or press Ctrl+P (Windows) / Cmd+P (Mac)</small>
                        </div>
                    </div>
                </div>
                
                <script>
                    // Auto-print after 1 second
                    setTimeout(function() {
                        window.print();
                    }, 1000);
                    
                    // Close window after printing (optional)
                    window.onafterprint = function() {
                        setTimeout(function() {
                            window.close();
                        }, 1000);
                    };
                <\/script>
            </body>
            </html>`;
            
            // Open in new window for printing
            const printWindow = window.open('', '_blank', 'width=1200,height=800');
            printWindow.document.write(html);
            printWindow.document.close();
            
        } catch (error) {
            console.error('Fallback also failed:', error);
            alert('Both PDF methods failed. Please try the print option manually.');
        }
    }

    // Function to reset PDF button
    function resetPDFButton() {
        const pdfBtn = document.getElementById('pdfBtn');
        const pdfLoading = document.getElementById('pdfLoading');
        
        if (pdfBtn) {
            pdfBtn.disabled = false;
            pdfBtn.innerHTML = '📄 Download PDF';
        }
        if (pdfLoading) {
            pdfLoading.style.display = 'none';
        }
    }

    // Check if jsPDF is loaded on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Test if jsPDF is loaded
        setTimeout(() => {
            if (typeof window.jspdf === 'undefined') {
                console.warn('jsPDF not loaded. PDF generation may not work.');
                const pdfSection = document.querySelector('.pdf-download-section');
                if (pdfSection) {
                    const warning = document.createElement('div');
                    warning.className = 'error-message';
                    warning.textContent = 'PDF library not loaded. Please check internet connection.';
                    warning.style.cssText = 'background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-top: 10px;';
                    pdfSection.appendChild(warning);
                }
            }
        }, 1000);
        
        // Set today's date as default in the date input
        const dateInput = document.querySelector('input[name="expense_date"]');
        if (dateInput) {
            const today = new Date().toISOString().split('T')[0];
            dateInput.value = today;
        }
    });
</script>


<?php include __DIR__ . '/../footer.php'; ?>
</body>
</html>