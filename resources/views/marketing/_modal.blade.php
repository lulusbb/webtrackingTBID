<div class="modal fade" id="modalTambahKlien" tabindex="-1" aria-labelledby="modalTambahKlienLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content bg-gray-900 text-white rounded-lg">
      <div class="modal-header border-gray-700">
        <h5 class="modal-title">Tambah Data Klien</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <form action="{{ route('marketing.klien.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="modal-body max-h-[75vh] overflow-y-auto px-4 py-2">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6">
            @php
              $fields = [
                ['nama', 'Nama'],
                ['lokasi_lahan', 'Lokasi Lahan'],
                ['luas_lahan', 'Luas Lahan', 'number'],
                ['luas_bangunan', 'Luas Bangunan', 'number'],
                ['kebutuhan_ruang', 'Kebutuhan Ruang'],
                ['sertifikat', 'Sertifikat', 'file'],
                ['arah_mata_angin', 'Arah Mata Angin'],
                ['batas_keliling', 'Batas Keliling Bangunan'],
                ['foto_eksisting', 'Foto Eksisting', 'file'],
                ['konsep_bangunan', 'Konsep Bangunan'],
                ['referensi', 'Referensi', 'file'],
                ['budget', 'Budget'],
                ['share_lokasi', 'Share Lokasi'],
                ['biaya_survei', 'Biaya Survei'],
                ['hoby', 'Hoby Klien'],
                ['aktivitas', 'Aktivitas Klien'],
                ['prioritas_ruang', 'Prioritas Ruang'],
                ['kendaraan', 'Jenis & Jumlah Kendaraan'],
                ['estimasi_start', 'Estimasi Start Bangun', 'date'],
                ['target_user_kos', 'Target User Kos'],
                ['fasilitas_kos', 'Fasilitas Umum Kos'],
                ['layout', 'Layout', 'file'],
                ['desain_3d', '3D Desain'],
                ['rab_boq', 'RAB / BOQ'],
                ['gambar_kerja', 'Gambar Kerja'],
              ];
            @endphp

            @foreach ($fields as $field)
              @php
                $name = $field[0];
                $label = $field[1];
                $type = $field[2] ?? 'text';
              @endphp

              <div class="space-y-1 mb-4">
                <label for="{{ $name }}" class="block font-medium">{{ $label }}</label>
                <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
                  class="w-full px-3 py-2 rounded border border-gray-700 bg-gray-800 text-white focus:outline-none focus:ring focus:border-blue-500"
                >
              </div>
            @endforeach
          </div>
        </div>

        <div class="modal-footer border-gray-700">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
