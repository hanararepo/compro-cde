<div class="space-y-2 border-t border-slate-100 pt-4">
    <label for="is_closed" class="block text-sm font-semibold text-slate-700">Recruitment status</label>
    <select name="is_closed" id="is_closed" class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-4 focus:ring-brand-100 focus:border-brand-500">
        <option value="0" @selected((string) old('is_closed', isset($career) && $career->is_closed ? '1' : '0') === '0')>Open — menerima lamaran</option>
        <option value="1" @selected((string) old('is_closed', isset($career) && $career->is_closed ? '1' : '0') === '1')>Closed — lamaran ditutup</option>
    </select>
    <p class="text-xs leading-relaxed text-slate-500">Lowongan Closed tetap tampil di publik jika Publish dicentang, tetapi tidak bisa menerima lamaran baru.</p>
    @error('is_closed')<p class="text-xs text-rose-600">{{ $message }}</p>@enderror
</div>
