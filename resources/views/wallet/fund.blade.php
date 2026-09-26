<x-app-layout>
    <div class="max-w-md mx-auto px-4 py-8">

        {{-- 1. Header & Back Button --}}
        <div class="flex items-center justify-between mb-8">
            <a href="{{ route('dashboard') }}" class="group flex items-center gap-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                <div class="w-8 h-8 rounded-full bg-card border border-border flex items-center justify-center group-hover:border-primary/50 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </div>
                <span>Back</span>
            </a>
            <h1 class="text-lg font-bold">Fund Wallet</h1>
            <div class="w-8"></div> {{-- Spacer --}}
        </div>

        {{-- 2. Main Funding Card --}}
        <div class="bg-card border border-border rounded-3xl overflow-hidden shadow-xl" 
             x-data="{ 
                tab: 'demo', 
                amount: '',
                copyToClipboard(text) {
                    navigator.clipboard.writeText(text);
                    alert('Account number copied!');
                }
             }">
            
            {{-- TABS: Strategy Implementation (Instant vs Manual) --}}
            <div class="grid grid-cols-2 border-b border-border bg-secondary/20">
                <button @click="tab = 'demo'" 
                        :class="tab === 'demo' ? 'text-primary border-b-2 border-primary bg-primary/5' : 'text-muted-foreground hover:text-foreground hover:bg-secondary/40'"
                        class="py-4 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                    <x-heroicon-m-credit-card class="w-4 h-4" />
                    Instant Pay
                </button>
                <button @click="tab = 'manual'" 
                        :class="tab === 'manual' ? 'text-primary border-b-2 border-primary bg-primary/5' : 'text-muted-foreground hover:text-foreground hover:bg-secondary/40'"
                        class="py-4 text-sm font-semibold transition-all flex items-center justify-center gap-2">
                    <x-heroicon-m-building-library class="w-4 h-4" />
                    Manual Transfer
                </button>
            </div>

            <div class="p-6 min-h-[400px]">
                
                {{-- TAB 1: instant (demo gateway) --}}
                <div x-show="tab === 'demo'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2">
                    <form method="POST" action="{{ route('wallet.fund') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="payment_method" value="demo">
                        
                        {{-- Recommendation Badge --}}
                        <div class="text-center space-y-2 mb-6">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 text-[10px] font-bold uppercase tracking-wider border border-emerald-500/20">
                                <span class="relative flex h-2 w-2">
                                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                  <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                Fastest Option
                            </div>
                        </div>

                        {{-- Amount Input --}}
                        <div>
                            <label class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-2 block">Amount to Fund</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-lg font-bold text-muted-foreground">₦</span>
                                <input type="number" name="amount" x-model="amount" placeholder="0.00" required min="100"
                                       class="w-full bg-gray-50 dark:bg-white/5 border border-border rounded-xl pl-10 pr-4 py-4 text-2xl font-bold 
                                              focus:ring-2 focus:ring-primary focus:border-transparent transition-all placeholder:text-muted-foreground/30">
                            </div>
                        </div>

                        {{-- Quick Amount Chips --}}
                        <div class="flex flex-wrap gap-2 justify-center">
                            @foreach([1000, 2000, 5000, 10000] as $chip)
                                <button type="button" @click="amount = {{ $chip }}"
                                        class="px-4 py-2 rounded-lg border border-border bg-secondary/30 text-xs font-medium hover:border-primary hover:text-primary active:scale-95 transition-all">
                                    ₦{{ number_format($chip) }}
                                </button>
                            @endforeach
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" 
                                :disabled="!amount || amount < 100"
                                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-emerald-500/20 disabled:opacity-50 disabled:shadow-none transition-all flex items-center justify-center gap-2 mt-4">
                            <span>Pay with demo</span>
                            <x-heroicon-m-arrow-right class="w-5 h-5" />
                        </button>
                        
                        <div class="flex items-center justify-center gap-2 opacity-50 grayscale mt-4">
                            <x-heroicon-s-lock-closed class="w-3 h-3" />
                            <span class="text-[10px] text-muted-foreground">portfolio demo gateway</span>
                        </div>
                    </form>
                </div>

                {{-- TAB 2: MANUAL TRANSFER (BACKUP) --}}
                <div x-show="tab === 'manual'" style="display: none;" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2">
                    
                    {{-- Warning Box --}}
                    <div class="rounded-xl bg-orange-500/10 border border-orange-500/20 p-4 flex gap-3 mb-6">
                        <x-heroicon-m-clock class="w-5 h-5 text-orange-500 shrink-0" />
                        <div>
                            <h4 class="text-sm font-bold text-orange-500">Admin Approval Required</h4>
                            <p class="text-[11px] text-muted-foreground mt-1 leading-tight">
                                Manual transfers are not instant. We confirm payments within 30-60 minutes (9am - 9pm).
                            </p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('wallet.fund') }}" class="space-y-6">
                        @csrf
                        <input type="hidden" name="payment_method" value="bank_transfer">

                        {{-- Bank Details Card --}}
                        <div class="bg-secondary/30 rounded-xl p-5 border border-dashed border-border text-center space-y-1 relative group">
                            <p class="text-[10px] text-muted-foreground uppercase tracking-wider">Bank Name</p>
                            <p class="font-bold text-foreground">Moniepoint MFB</p>
                            
                            <div class="h-4"></div>

                            <p class="text-[10px] text-muted-foreground uppercase tracking-wider">Account Number</p>
                            <div class="flex items-center justify-center gap-2 cursor-pointer p-2 rounded-lg hover:bg-white/5 transition" 
                                 @click="copyToClipboard('1234567890')"> <span class="text-2xl font-mono font-bold tracking-widest text-primary">123 456 7890</span>
                                <x-heroicon-m-document-duplicate class="w-4 h-4 text-muted-foreground group-hover:text-primary transition-colors" />
                            </div>
                            <div class="text-[10px] text-primary opacity-0 group-hover:opacity-100 transition-opacity">Click to copy</div>

                            <div class="h-2"></div>

                            <p class="text-[10px] text-muted-foreground uppercase tracking-wider">Account Name</p>
                            <p class="font-bold text-foreground">Fatori Tech Solutions</p>
                        </div>

                        {{-- Manual Input --}}
                        <div class="space-y-4">
                            <div>
                                <label class="text-xs font-semibold text-muted-foreground">Amount Sent</label>
                                <input type="number" name="amount" required placeholder="How much did you send?"
                                       class="w-full bg-gray-50 dark:bg-white/5 border border-border rounded-xl px-4 py-3 text-sm focus:ring-primary">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-secondary hover:bg-secondary/80 text-foreground font-semibold py-3 rounded-xl border border-border transition-colors">
                            I Have Made The Transfer
                        </button>
                    </form>
                </div>

            </div>
        </div>
        
        {{-- Footer Note --}}
        <p class="text-center text-xs text-muted-foreground mt-6 opacity-60">
            Deposits are final and cannot be reversed.
        </p>

    </div>
</x-app-layout>
