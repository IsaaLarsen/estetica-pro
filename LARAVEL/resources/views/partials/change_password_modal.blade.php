<!-- Modal: Alterar senha do usuário logado -->
<style>
  .ep-modal-overlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;z-index:1100;background:rgba(0,0,0,.35)}
  .ep-card{background:#fff;width:100%;max-width:460px;border-radius:16px;padding:22px;box-shadow:0 20px 40px rgba(0,0,0,.18);font-family:'Poppins',system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial}
  .ep-title{margin:0 0 6px;font-weight:700;font-size:18px;color:#1f2937}
  .ep-sub{margin:0 0 14px;color:#6b7280;font-size:13px}
  .ep-row{display:flex;flex-direction:column;gap:10px;margin-bottom:10px}
  .ep-input{width:100%;padding:12px 14px;border:2px solid #e5e7eb;border-radius:12px;font-size:14px;outline:none;transition:border .2s, box-shadow .2s}
  .ep-input:focus{border-color:#ec4899;box-shadow:0 0 0 3px rgba(236,72,153,.18)}
  .ep-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:8px}
  .ep-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border-radius:12px;border:0;cursor:pointer;font-weight:600;font-size:14px}
  .ep-btn-cancel{background:#f3f4f6;color:#374151}
  .ep-btn-primary{background:linear-gradient(135deg,#ec4899 0%,#7e22ce 100%);color:#fff;box-shadow:0 6px 18px rgba(236,72,153,.35)}
  .ep-btn-primary:hover{transform:translateY(-1px)}
  .ep-error{font-size:12px;color:#b91c1c;margin-top:-6px;margin-bottom:6px;display:none}
  .ep-error.show{display:block}
</style>

<div id="meChangePwdOverlay" class="ep-modal-overlay" aria-hidden="true">
  <div class="ep-card" role="dialog" aria-modal="true" aria-labelledby="epModalTitle">
    <h3 id="epModalTitle" class="ep-title">Alterar senha</h3>
    <p class="ep-sub">Informe sua senha atual e defina a nova. Use 6+ caracteres.</p>

    <form id="meChangePwdForm" method="POST" action="{{ route('me.senha.update') }}">
      @csrf
      <div class="ep-row">
        <input class="ep-input" type="password" name="senha_atual" placeholder="Senha atual" required autocomplete="current-password">
        <span id="err_atual" class="ep-error">Senha atual inválida.</span>
      </div>

      <div class="ep-row">
        <input class="ep-input" type="password" name="nova_senha" id="nova_senha" placeholder="Nova senha" minlength="6" required autocomplete="new-password">
      </div>

      <div class="ep-row">
        <input class="ep-input" type="password" name="nova_senha_confirmation" id="nova_senha_confirmation" placeholder="Confirmar nova senha" minlength="6" required autocomplete="new-password">
        <span id="err_match" class="ep-error">As senhas não conferem.</span>
      </div>

      <div class="ep-actions">
        <button type="button" id="meChangePwdCancel" class="ep-btn ep-btn-cancel">Cancelar</button>
        <button type="submit" class="ep-btn ep-btn-primary">
          <i class="fas fa-key"></i> Salvar
        </button>
      </div>
    </form>
  </div>
</div>

<script>
(()=>{
  const overlay = document.getElementById('meChangePwdOverlay');
  const form = document.getElementById('meChangePwdForm');
  const btnCancel = document.getElementById('meChangePwdCancel');
  const open = ()=>{ overlay.style.display='flex'; overlay.setAttribute('aria-hidden','false'); };
  const close = ()=>{ overlay.style.display='none'; overlay.setAttribute('aria-hidden','true'); form.reset(); clearErrors(); };

  const trigger = document.getElementById('editPasswordBtn');
  trigger?.addEventListener('click', e=>{ e.preventDefault(); e.stopPropagation(); open(); });

  document.querySelectorAll('[data-action="open-change-password"]').forEach(btn=>{
    btn.addEventListener('click', e=>{ e.preventDefault(); open(); });
  });

  overlay?.addEventListener('click', e=>{ if (e.target===overlay) close(); });
  btnCancel?.addEventListener('click', close);
  document.addEventListener('keydown', e=>{ if(e.key==='Escape') close(); });

  const errMatch = document.getElementById('err_match');
  const errAtual = document.getElementById('err_atual');
  const nova = document.getElementById('nova_senha');
  const conf = document.getElementById('nova_senha_confirmation');

  function clearErrors(){ errMatch?.classList.remove('show'); errAtual?.classList.remove('show'); }
  function same(){ return (nova?.value||'') === (conf?.value||''); }

  conf?.addEventListener('input', ()=>{ if(!same()) errMatch.classList.add('show'); else errMatch.classList.remove('show'); });
  nova?.addEventListener('input', ()=>{ if(conf.value && !same()) errMatch.classList.add('show'); else errMatch.classList.remove('show'); });

  form?.addEventListener('submit', (e)=>{
    clearErrors();
    if(!same()){
      errMatch.classList.add('show');
      e.preventDefault();
      return false;
    }
    // deixa o back validar senha atual; se vier erro de sessão, o toast mostrará.
  });
})();
</script>
