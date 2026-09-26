<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('theme', 'dark') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt - {{ $audit->displayReference() }}</title>
    
    {{-- ✅ Load Styles (Tailwind) without the Dashboard Layout --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#0B1120] text-gray-300 font-sans antialiased min-h-screen flex items-center justify-center p-4">

    @php
        $appName = config('app.name', 'Fatori Tech Solutions');
        $status  = $audit->status ?? 'success';
        $isCredit = $audit->direction === 'credit';
        
        // HIDE AMOUNT LOGIC: Hide if source is 'data'
        $showAmount = $audit->source !== 'data'; 

        // Status Colors
        $headerColor = match ($status) {
            'success' => 'bg-[#10b981]', // Emerald-500
            'failed'  => 'bg-[#ef4444]', // Red-500
            'pending' => 'bg-[#f59e0b]', // Amber-500
            default   => 'bg-[#6b7280]', // Gray-500
        };

        // Extract Metadata
        $meta = is_string($audit->meta) ? json_decode($audit->meta, true) : ($audit->meta ?? []);
        $meta = collect($meta);
        
        $token = $meta->get('token');
        $pins  = $meta->get('pins');
        
        // Filter out technical fields
        $details = $meta->except(['token', 'pins', 'gateway_response', 'payload', 'gateway_data', 'channel', 'service_code', 'service_name', 'unit_price', 'quantity', 'delivery_email']);
    @endphp

    <div class="w-full max-w-md">
        
        {{-- RECEIPT CARD --}}
        <div id="receipt-card" class="bg-[#0B1120] rounded-3xl overflow-hidden shadow-2xl relative border border-gray-800">
            
            {{-- 1. HEADER --}}
            <div class="{{ $headerColor }} p-8 text-white relative overflow-hidden">
                {{-- Decorative Blob --}}
                <div class="absolute -top-10 -right-10 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>

                <div class="relative z-10">
                    <h1 class="text-2xl font-bold mb-1">Transaction Receipt</h1>
                    
                    <p class="text-xs text-white/80 mb-1 mt-4">Reference</p>
                    <div class="font-mono text-sm bg-black/10 rounded-lg px-3 py-2 break-all border border-white/10 inline-block">
                        {{ $audit->displayReference() }}
                    </div>                    

                    <div class="absolute top-8 right-8">
                        <span class="px-3 py-1 rounded-full bg-black/20 backdrop-blur-sm text-xs font-bold border border-white/10 uppercase tracking-wide">
                            {{ $status }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- 2. MAIN BODY --}}
            <div class="p-8 space-y-6 bg-[#0f1623]">
                
                <div class="space-y-4 text-sm">
                    <div class="flex justify-between items-center text-gray-400">
                        <span>Customer</span>
                        <span class="text-white font-medium text-right">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-400">
                        <span>Type</span>
                        <span class="text-white font-medium text-right capitalize">{{ str_replace('_', ' ', $audit->source) }}</span>
                    </div>
                    <div class="flex justify-between items-center text-gray-400">
                        <span>Date</span>
                        <span class="text-white font-medium text-right">
                            {{ optional($audit->created_at)->format('d M Y, h:i A') ?? '—' }}
                        </span>                        
                    </div>
                    
                    {{-- CONDITIONAL AMOUNT DISPLAY --}}
                    @if($showAmount)
                        <div class="flex justify-between items-center pt-2 border-t border-gray-800 mt-2">
                            <span class="text-gray-400">Amount</span>
                            <span class="text-xl font-bold {{ $isCredit ? 'text-emerald-400' : 'text-white' }}">
                                {{ $isCredit ? '+' : '-' }}₦{{ number_format(abs($audit->amount), 2) }}
                            </span>
                        </div>
                    @endif

                    <div class="flex justify-between items-start text-gray-400">
                        <span>Description</span>
                        <span class="text-white font-medium text-right max-w-[200px] leading-tight">{{ $audit->description }}</span>
                    </div>
                </div>

                {{-- 3. DYNAMIC DETAILS --}}
                @if($details->isNotEmpty())
                    <div class="pt-6 border-t border-gray-800">
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-3">Transaction Details</p>
                        <div class="space-y-3 text-sm">
                            @foreach($details as $key => $value)
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="text-white font-medium text-right">
                                        {{ is_array($value) ? implode(', ', $value) : $value }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- 4. TOKENS / PINS --}}
                @if($token || !empty($pins))
                    <div class="mt-4 bg-gray-800/50 rounded-xl p-4 border border-gray-700">
                        <p class="text-xs text-emerald-400 uppercase font-bold mb-2">
                            {{ $token ? 'Electricity Token' : 'Exam PIN(s)' }}
                        </p>
                        
                        @if($token)
                            <div class="flex justify-between items-center gap-2">
                                <span class="font-mono text-xl font-bold text-white tracking-widest">{{ $token }}</span>
                                <button onclick="copyText('{{ $token }}')" class="p-2 hover:bg-white/10 rounded-lg transition">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </div>
                        @endif

                        @if(!empty($pins))
                            @foreach((array)$pins as $pin)
                                <div class="flex justify-between items-center gap-2 mb-2 last:mb-0">
                                    <span class="font-mono text-lg font-bold text-white tracking-widest">{{ $pin }}</span>
                                    <button onclick="copyText('{{ $pin }}')" class="p-1.5 hover:bg-white/10 rounded-lg transition">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </button>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endif

                {{-- 5. FOOTER --}}
                <div class="pt-6 border-t border-gray-800 text-center">
                    <p class="text-white font-semibold text-sm">Thank you for choosing {{ $appName }}</p>
                    <p class="text-gray-500 text-xs mt-1">Secure • Fast • Reliable VTU Services</p>
                </div>
            </div>
        </div>

        {{-- ACTION BUTTONS --}}
        <div class="mt-6 flex flex-col gap-3">
            
            {{-- Row for Save & Share --}}
            <div class="flex gap-3">
                <button onclick="downloadReceipt()" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition-all active:scale-95 shadow-lg shadow-emerald-900/20">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Save
                </button>
                
                <button onclick="shareReceipt()" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-sm font-semibold transition-all active:scale-95 border border-gray-700">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    Share
                </button>
            </div>

            {{-- DONE BUTTON (Redirects to Dashboard) --}}
            {{-- target="_top" is CRITICAL: It forces the iframe to break out and reload the main window --}}
            <a href="{{ route('dashboard') }}" target="_top" class="w-full flex items-center justify-center gap-2 px-6 py-3.5 bg-white text-gray-900 hover:bg-gray-100 rounded-xl text-sm font-bold transition-all active:scale-95 shadow-lg">
                Done
            </a>

        </div>

    </div>

    {{-- SCRIPTS --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function copyText(text) {
            navigator.clipboard.writeText(text);
            alert('Copied: ' + text);
        }

        function downloadReceipt() {
            const card = document.getElementById('receipt-card');
            const btn = event.currentTarget;
            const originalText = btn.innerHTML;
            
            btn.innerHTML = 'Generating...';
            btn.disabled = true;

            html2canvas(card, {
                scale: 2,
                backgroundColor: null,
                useCORS: true
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Receipt-{{ $audit->displayReference() }}.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
                
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error(err);
                btn.innerHTML = 'Error!';
                setTimeout(() => { btn.innerHTML = originalText; btn.disabled = false; }, 2000);
            });
        }

        async function shareReceipt() {
            const text = `Transaction Receipt\nRef: {{ $audit->displayReference() }}\nStatus: {{ ucfirst($status) }}\n\nPowered by {{ $appName }}`;
            
            if (navigator.share) {
                try {
                    await navigator.share({
                        title: 'Transaction Receipt',
                        text: text,
                        url: window.location.href
                    });
                } catch (err) {
                    console.log('Share canceled');
                }
            } else {
                copyText(window.location.href);
                alert('Link copied to clipboard!');
            }
        }
    </script>
</body>
</html>