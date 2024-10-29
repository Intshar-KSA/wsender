<div id="qrCodeModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-5 rounded-lg">
        <h2 id="modalTitle" class="text-xl font-bold">QR Code</h2>
        <div id="modalBody" class="mt-4">
            <!-- سيظهر QR Code هنا -->
        </div>
        <button onclick="closeModal()" class="mt-4 bg-red-500 text-white p-2 rounded">إغلاق</button>
    </div>
</div>

<script>
    function openModal(title, body) {
        document.getElementById('modalTitle').textContent = title;
        document.getElementById('modalBody').innerHTML = body;
        document.getElementById('qrCodeModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('qrCodeModal').classList.add('hidden');
    }

    window.addEventListener('open-modal', event => {
        openModal(event.detail.title, event.detail.body);
    });
</script>
