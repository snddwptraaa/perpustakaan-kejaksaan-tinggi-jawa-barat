#!/usr/bin/env python3
"""Uji field form kunjungan dan submit Livewire melalui HTTP."""
import html
import json
import re
import requests

BASE = "http://127.0.0.1:8000"
s = requests.Session()
s.headers.update({"User-Agent": "Mozilla/5.0", "Accept": "text/html,application/xhtml+xml"})

print("=" * 60)
print("TEST FORM KUNJUNGAN TAMU VIA HTTP/LIVEWIRE")
print("=" * 60)

page = s.get(BASE + "/kunjungan")
print(f"[1] GET /kunjungan: HTTP {page.status_code}")
assert page.status_code == 200

csrf = re.search(r'name="csrf-token"\s+content="([^"]+)"', page.text).group(1)
snapshot_raw = re.search(r'wire:snapshot="([^"]+)"', page.text).group(1)
snapshot = html.unescape(snapshot_raw)
snapshot_obj = json.loads(snapshot)
component_id = re.search(r'wire:id="([^"]+)"', page.text).group(1)
print(f"    CSRF ditemukan, component={component_id}")

# Semua field diisi sekaligus lalu action submit dipanggil.
state = {
    "kategori": "pegawai",
    "nama": "Test Pegawai Kejati",
    "nip": "199001012020121001",
    "instansi_unit": "Bidang Pidana Kejati Jabar",
    "no_hp": "08123456789",
    "keperluan": "Mencari referensi perkara hukum",
    "accepted_privacy": True,
}
updates = [{"type": "syncInput", "payload": {"name": k, "value": v}} for k, v in state.items()]
payload = {
    "components": [{
        "snapshot": snapshot,
        "updates": updates,
        "calls": [{"path": "", "method": "submit", "params": [], "uuid": "test-visitor"}],
    }]
}
response = s.post(
    BASE + "/livewire/update",
    json=payload,
    headers={"X-Livewire": "true", "X-CSRF-TOKEN": csrf, "Referer": BASE + "/kunjungan"},
)
print(f"[2] Isi 7 field + submit: HTTP {response.status_code}")
print(f"    response={response.text[:500]}")
assert response.status_code == 200, response.text

body = response.json()
redirect = body.get("components", [{}])[0].get("effects", {}).get("redirect")
print(f"    redirect={redirect}")
assert redirect and "/katalog" in redirect, "Submit tidak mengarahkan ke katalog"

catalog = s.get(BASE + "/katalog")
print(f"[3] GET /katalog setelah submit: HTTP {catalog.status_code}")
assert catalog.status_code == 200
print("    OK: session check-in aktif dan katalog dapat dibuka")

ready = s.get(BASE + "/ready")
print(f"[4] GET /ready: HTTP {ready.status_code} {ready.text}")
assert ready.status_code == 200
print("✅ TEST FORM KUNJUNGAN BERHASIL")
print("=" * 60)

# Catatan: session test bersifat terisolasi di script dan tidak mengubah source code aplikasi.
