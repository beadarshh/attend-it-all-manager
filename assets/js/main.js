
// User dropdown toggle
document.addEventListener('DOMContentLoaded', function() {
    // Mobile navigation toggle
    const menuButton = document.querySelector('.menu-button');
    const sidebar = document.querySelector('.sidebar');
    const sidebarClose = document.querySelector('.sidebar-close');
    
    if (menuButton && sidebar) {
        menuButton.addEventListener('click', function() {
            sidebar.classList.add('active');
        });
        
        sidebarClose.addEventListener('click', function() {
            sidebar.classList.remove('active');
        });
    }
    
    // User dropdown
    const userButton = document.querySelector('.user-button');
    const userMenu = document.querySelector('.user-menu');
    
    if (userButton && userMenu) {
        userButton.addEventListener('click', function(e) {
            e.preventDefault();
            userMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!userButton.contains(e.target) && !userMenu.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
    }
    
    // File upload
    const fileUpload = document.querySelector('.file-upload');
    const fileInput = document.querySelector('.file-input');
    
    if (fileUpload && fileInput) {
        // File input change
        fileInput.addEventListener('change', function() {
            if (this.files.length > 0) {
                const fileName = this.files[0].name;
                document.querySelector('.file-name').textContent = fileName;
                document.querySelector('.file-upload-form').submit();
            }
        });
        
        // Drag and drop
        fileUpload.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('drag-over');
        });
        
        fileUpload.addEventListener('dragleave', function() {
            this.classList.remove('drag-over');
        });
        
        fileUpload.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('drag-over');
            
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const fileName = e.dataTransfer.files[0].name;
                document.querySelector('.file-name').textContent = fileName;
                document.querySelector('.file-upload-form').submit();
            }
        });
    }
    
    // Attendance status toggle
    const attendanceRadios = document.querySelectorAll('.attendance-radio');
    
    if (attendanceRadios.length > 0) {
        attendanceRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const row = this.closest('tr');
                
                // Remove existing status classes
                row.classList.remove('status-present', 'status-absent', 'status-leave');
                
                // Add new status class
                if (this.value === 'present') {
                    row.classList.add('status-present');
                } else if (this.value === 'absent') {
                    row.classList.add('status-absent');
                } else if (this.value === 'leave') {
                    row.classList.add('status-leave');
                }
            });
        });
    }
});

// Date formatter
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

// CSV export function
function exportTableToCSV(tableId, filename) {
    const table = document.getElementById(tableId);
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            // Remove HTML from cell content
            const cellContent = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellContent + '"');
        }
        
        csv.push(row.join(','));
    }
    
    // Download CSV file
    downloadCSV(csv.join('\n'), filename);
}

function downloadCSV(csv, filename) {
    const csvFile = new Blob([csv], {type: "text/csv"});
    const downloadLink = document.createElement("a");
    
    // File name
    downloadLink.download = filename + '.csv';
    
    // Create a link to the file
    downloadLink.href = window.URL.createObjectURL(csvFile);
    
    // Hide download link
    downloadLink.style.display = "none";
    
    // Add the link to DOM
    document.body.appendChild(downloadLink);
    
    // Click download link
    downloadLink.click();
    
    // Remove link from DOM
    document.body.removeChild(downloadLink);
}
