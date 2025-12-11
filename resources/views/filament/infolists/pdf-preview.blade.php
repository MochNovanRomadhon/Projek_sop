@php
    $filePath = $getState();
    $url = $filePath ? \Illuminate\Support\Facades\Storage::url($filePath) : null;
@endphp

<div class="w-full">
    @if($url)

        <div 
            class="w-full border border-gray-300 rounded-lg overflow-hidden bg-gray-100"
            style="height: 1200px; min-height: 1200px;" 
        >
            <iframe 
                src="{{ $url }}" 
                width="100%" 
                height="100%" 
                style="border: none; height: 100%; width: 100%;"
                title="PDF Preview"
            ></iframe>
        </div>

    @else
        <div class="flex items-center justify-center h-32 border-2 border-dashed border-gray-300 rounded-lg bg-gray-50">
            <p class="text-gray-500">Tidak ada dokumen yang diunggah.</p>
        </div>
    @endif
</div>