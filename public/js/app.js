// SAMS global JavaScript helpers (plain JS, no build step).
// Alpine.js is loaded separately via CDN in the layout.

window.showToast = function (message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500' :
        type === 'error' ? 'bg-red-500' :
        'bg-yellow-500'
    } text-white`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
};

window.confirmDelete = function (message = 'Are you sure you want to delete this item?') {
    return confirm(message);
};

window.formatDate = function (date) {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

window.copyToClipboard = function (text) {
    navigator.clipboard.writeText(text).then(() => {
        showToast('Copied to clipboard!', 'success');
    }).catch(() => {
        showToast('Failed to copy', 'error');
    });
};

window.exportTableToCSV = function (tableId, filename = 'export.csv') {
    const table = document.getElementById(tableId);
    const rows = table.querySelectorAll('tr');
    const csv = [];
    rows.forEach(row => {
        const cols = row.querySelectorAll('td, th');
        const rowData = Array.from(cols).map(col => '"' + col.textContent.trim().replace(/"/g, '""') + '"');
        csv.push(rowData.join(','));
    });
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    a.click();
    window.URL.revokeObjectURL(url);
};

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfMeta ? csrfMeta.content : '';

window.api = {
    async get(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin'
        });
        return response.json();
    },
    async post(url, data) {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        });
        return response.json();
    },
    async put(url, data) {
        const response = await fetch(url, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin',
            body: JSON.stringify(data)
        });
        return response.json();
    },
    async delete(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            credentials: 'same-origin'
        });
        return response.json();
    }
};

console.log('SAMS Application Loaded');
