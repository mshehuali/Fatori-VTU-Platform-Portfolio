<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>

<script>
/* -----------------------------
   TOAST
----------------------------- */
function showToast(message = 'Copied') {
    const toast = document.createElement("div");
    toast.innerHTML = `✔ ${message}`;
    toast.className = `
        fixed bottom-6 right-6 z-[9999]
        bg-slate-900 text-white
        px-4 py-2 rounded-xl
        shadow-xl text-sm font-semibold
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 1800);
}

/* -----------------------------
   COPY HELPERS
----------------------------- */
function copyText(value) {
    if (!value) return;

    if (navigator.clipboard) {
        navigator.clipboard.writeText(value)
            .then(() => showToast('Copied'))
            .catch(() => showToast('Copy failed'));
    } else {
        const t = document.createElement("textarea");
        t.value = value;
        document.body.appendChild(t);
        t.select();
        document.execCommand("copy");
        document.body.removeChild(t);
        showToast('Copied');
    }
}

function copyReference() {
    const refEl = document.getElementById('receipt-reference');
    if (!refEl) return;
    copyText(refEl.innerText.trim());
}

/* -----------------------------
   WHATSAPP (LINK)
----------------------------- */
function shareWhatsAppImage() {
    const ref = document.getElementById('receipt-reference')?.innerText || '';
    const url = window.location.href;
    window.open(
        `https://wa.me/?text=Receipt%20(${ref})%20-%20${url}`,
        '_blank'
    );
}

/* -----------------------------
   DOWNLOAD / SHARE IMAGE
----------------------------- */
async function downloadReceiptImage(useShare = false) {
    const receipt = document.getElementById('receipt-main');
    if (!receipt) {
        alert('Receipt not found');
        return;
    }

    const clone = receipt.cloneNode(true);

    /* Remove balances in export */
    clone.querySelectorAll('.balance-label').forEach(e => e.remove());

    // 🔥 REMOVE COPY BUTTONS
    clone.querySelectorAll('.copy-btn').forEach(btn => btn.remove());

    const temp = document.createElement('div');
    temp.style.cssText = `
        position:fixed;
        top:-9999px;
        left:0;
        width:390px;
        background:#ffffff;
        z-index:9999;
    `;
    temp.appendChild(clone);
    document.body.appendChild(temp);

    try {
        const canvas = await html2canvas(clone, {
            scale: 2,
            backgroundColor: '#ffffff'
        });

        const dataUrl = canvas.toDataURL('image/png');
        const ref = document.getElementById('receipt-reference')
            ?.innerText.replace(/[^a-z0-9]/gi,'_') || 'receipt';

        const filename = `FatoriTechReceipt_${ref}.png`;

        if (useShare && navigator.share) {
            const blob = await (await fetch(dataUrl)).blob();
            await navigator.share({
                title: 'Transaction Receipt',
                files: [new File([blob], filename, { type: 'image/png' })]
            });
        } else {
            const a = document.createElement('a');
            a.href = dataUrl;
            a.download = filename;
            document.body.appendChild(a);
            a.click();
            a.remove();
        }
    } finally {
        document.body.removeChild(temp);
    }
}

function shareViaApps() {
    downloadReceiptImage(true);
}
</script>
