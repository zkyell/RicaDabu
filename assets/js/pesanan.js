// Halaman status pesanan: upload bukti, bayar online, cek status otomatis
const O = $("order")?.dataset;
if(O){
  // Simpan kode ke riwayat HP ini (misalnya kalau dibuka dari link)
  MyOrders.set([O.code, ...MyOrders.get().filter(c=>c!==O.code)].slice(0,30));
  if(new URLSearchParams(location.search).has("baru")) history.replaceState(null, "", "pesanan.php?kode=" + O.code);

  // Cek perubahan status setiap 10 detik; muat ulang halaman bila berubah
  let busy = false;
  setInterval(async () => {
    if(busy || document.hidden) return;
    try{
      const r = await fetch("api/status.php?kode=" + O.code, {cache:"no-store"});
      const [o] = await r.json();
      if(o && o.status + "|" + o.payment_status !== O.sig) location.reload();
    }catch(e){}
  }, 10000);

  // Upload bukti pembayaran
  const f = $("upForm");
  if(f){
    $("bukti").onchange = e => {
      const file = e.target.files[0]; if(!file) return;
      if(file.size > 3*1024*1024){ $("upMsg").textContent = "Ukuran foto maksimal 3 MB."; e.target.value=""; return; }
      $("upPrev").src = URL.createObjectURL(file); $("upPrev").hidden = false;
      $("upTxt").textContent = "📎 " + file.name; $("upBtn").disabled = false; $("upMsg").textContent = "";
    };
    f.onsubmit = async e => {
      e.preventDefault(); busy = true;
      const b = $("upBtn"); b.disabled = true; b.textContent = "Mengunggah…";
      try{
        const r = await fetch("api/upload-bukti.php", {method:"POST", body:new FormData(f)});
        const j = await r.json();
        if(!r.ok) throw new Error(j.error || "Gagal mengunggah");
        toast("Bukti pembayaran terkirim"); setTimeout(()=>location.reload(), 700);
      }catch(ex){ $("upMsg").textContent = ex.message; b.disabled = false; b.textContent = "Kirim Bukti Pembayaran"; busy = false; }
    };
  }

  // Bayar online (Midtrans Snap)
  const pay = $("payOnline");
  if(pay){
    const loadSnap = () => new Promise((ok, no) => {
      if(window.snap) return ok();
      const s = document.createElement("script");
      s.src = pay.dataset.snap; s.dataset.clientKey = pay.dataset.client;
      s.setAttribute("data-client-key", pay.dataset.client);
      s.onload = ok; s.onerror = () => no(new Error("Gagal memuat halaman pembayaran"));
      document.head.appendChild(s);
    });
    pay.onclick = async () => {
      pay.disabled = true; pay.textContent = "Menyiapkan pembayaran…"; busy = true;
      try{
        await loadSnap();
        const r = await fetch("api/midtrans-token.php", {method:"POST", headers:{"Content-Type":"application/json"}, body:JSON.stringify({kode:O.code})});
        const j = await r.json();
        if(!r.ok) throw new Error(j.error || "Gagal membuat pembayaran");
        window.snap.pay(j.token, {
          onSuccess: () => { toast("Pembayaran berhasil"); setTimeout(()=>location.reload(), 1500); },
          onPending: () => { toast("Selesaikan pembayaranmu"); },
          onError:   () => { toast("Pembayaran gagal"); },
          onClose:   () => { pay.disabled = false; pay.textContent = "Bayar Sekarang"; busy = false; }
        });
      }catch(ex){ toast(ex.message); pay.disabled = false; pay.textContent = "Bayar Sekarang"; busy = false; }
    };
  }
}
