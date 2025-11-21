<style>
  .ep-toast{position:fixed;right:18px;top:18px;z-index:1200;display:flex;flex-direction:column;gap:8px;font-family:'Poppins',system-ui,-apple-system}
  .ep-t{min-width:240px;max-width:360px;padding:10px 14px;border-radius:12px;color:#111827;box-shadow:0 10px 25px rgba(0,0,0,.12);display:flex;align-items:center;gap:8px;font-size:13px}
  .ep-t-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
  .ep-t-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}
  .ep-t i{min-width:16px}
</style>

<div class="ep-toast" id="epToast">
 @if(session('success'))
  <div class="ep-t ep-t-success"><i class="fas fa-check-circle"></i>{{ session('success') }}</div>
 @endif
 @if(session('error'))
  <div class="ep-t ep-t-error"><i class="fas fa-exclamation-circle"></i>{{ session('error') }}</div>
 @endif
 @if ($errors->any())
  <div class="ep-t ep-t-error"><i class="fas fa-exclamation-triangle"></i>{{ $errors->first() }}</div>
 @endif
</div>

<script>
  // esconde sozinho depois de 3s
  setTimeout(()=>{ document.getElementById('epToast')?.remove(); }, 3000);
</script>
