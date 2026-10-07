<nav x-data="{ open: false }" x-effect="document.body.classList.toggle('overflow-hidden', open)" @keydown.escape.window="open = false" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 lg:-my-px lg:ms-10 lg:flex">
                    @if (Auth::user()->canAccessMenu('dashboard'))<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-nav-link>@endif
                    @if (Auth::user()->canAccessMenu('clienti'))<x-nav-link :href="route('clienti')" :active="request()->routeIs('clienti')">{{ __('Clienti') }}</x-nav-link>@endif
                    @if (Auth::user()->canAccessMenu('viaggi'))<x-nav-link :href="route('viaggi.index')" :active="request()->routeIs('viaggi.*')">{{ __('Viaggi') }}</x-nav-link>@endif
                    @if (Auth::user()->canAccessMenu('calendario'))<x-nav-link :href="route('calendario')" :active="request()->routeIs('calendario*')">{{ __('Calendario') }}</x-nav-link>@endif
                    @if (Auth::user()->canAccessMenu('pratiche'))<x-nav-link :href="route('pratiche.index')" :active="request()->routeIs('pratiche.*')">{{ __('Pratiche') }}</x-nav-link>@endif
                    @if (Auth::user()->canAccessMenu('amministrazione'))<x-nav-link :href="route('amministrazione')" :active="request()->routeIs('amministrazione')">{{ __('Amministrazione') }}</x-nav-link>@endif
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden lg:ms-6 lg:flex lg:items-center">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        @if (Auth::user()->isAdmin())
                            <x-dropdown-link :href="route('utenti.index')">
                                {{ __('Utenti') }}
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('log.index')">
                                {{ __('Log') }}
                            </x-dropdown-link>
                        @endif

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center lg:hidden">
                <button type="button" @click="open = ! open" :aria-expanded="open" aria-controls="responsive-navigation" :aria-label="open ? 'Chiudi menu' : 'Apri menu'" class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-50 lg:hidden" id="responsive-navigation">
        <button type="button" @click="open = false" class="absolute inset-0 h-full w-full bg-black/50" aria-label="Chiudi menu"></button>
        <aside x-show="open" x-transition:enter="transform transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" role="dialog" aria-modal="true" aria-label="Menu principale" class="relative h-full w-4/5 max-w-sm overflow-y-auto bg-white shadow-xl">
            <div class="border-b border-gray-200 px-4 py-4">
                <div class="font-medium text-base text-gray-800">{{ Auth::user()->name }}</div>
                <div class="break-all font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="py-2">
                @if (Auth::user()->canAccessMenu('dashboard'))<x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">{{ __('Dashboard') }}</x-responsive-nav-link>@endif
                @if (Auth::user()->canAccessMenu('clienti'))<x-responsive-nav-link :href="route('clienti')" :active="request()->routeIs('clienti')">{{ __('Clienti') }}</x-responsive-nav-link>@endif
                @if (Auth::user()->canAccessMenu('viaggi'))<x-responsive-nav-link :href="route('viaggi.index')" :active="request()->routeIs('viaggi.*')">{{ __('Viaggi') }}</x-responsive-nav-link>@endif
                @if (Auth::user()->canAccessMenu('calendario'))<x-responsive-nav-link :href="route('calendario')" :active="request()->routeIs('calendario*')">{{ __('Calendario') }}</x-responsive-nav-link>@endif
                @if (Auth::user()->canAccessMenu('pratiche'))<x-responsive-nav-link :href="route('pratiche.index')" :active="request()->routeIs('pratiche.*')">{{ __('Pratiche') }}</x-responsive-nav-link>@endif
                @if (Auth::user()->canAccessMenu('amministrazione'))<x-responsive-nav-link :href="route('amministrazione')" :active="request()->routeIs('amministrazione')">{{ __('Amministrazione') }}</x-responsive-nav-link>@endif
            </div>

            <div class="border-t border-gray-200 py-2">
                <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>

                @if (Auth::user()->isAdmin())
                    <x-responsive-nav-link :href="route('utenti.index')">{{ __('Utenti') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('log.index')">{{ __('Log') }}</x-responsive-nav-link>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </aside>
    </div>
</nav>
