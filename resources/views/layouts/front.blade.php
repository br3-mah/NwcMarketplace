<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="Newword Market - Multivendor Ecommerce system">
    <meta name="author" content="NWCMarketplace">

    {{-- @dd('here') --}}
    @if(isset($page->meta_tag) && isset($page->meta_description))

		<meta name="keywords" content="{{ $page->meta_tag }}">
		<meta name="description" content="{{ $page->meta_description }}">
		<title>{{$gs->title}}</title>

	@elseif(isset($blog->meta_tag) && isset($blog->meta_description))

		<meta property="og:title" content="{{$blog->title}}" />
		<meta property="og:description" content="{{ $blog->meta_description != null ? $blog->meta_description : strip_tags($blog->meta_description) }}" />
		<meta property="og:image" content="{{asset('public/assets/images/blogs/'.$blog->photo)}}" />
		<meta name="keywords" content="{{ $blog->meta_tag }}">
		<meta name="description" content="{{ $blog->meta_description }}">
		<title>{{$gs->title}}</title>

	@elseif(isset($productt))

		<meta name="keywords" content="{{ !empty($productt->meta_tag) ? implode(',', $productt->meta_tag ): '' }}">
		<meta name="description" content="{{ $productt->meta_description != null ? $productt->meta_description : strip_tags($productt->description) }}">
		<meta property="og:title" content="{{$productt->name}}" />
		<meta property="og:description" content="{{ $productt->meta_description != null ? $productt->meta_description : strip_tags($productt->description) }}" />
		<meta property="og:image" content="{{asset('assets/images/thumbnails/'.$productt->thumbnail)}}" />
		<meta name="author" content="NWCMarketplace">
		<title>{{substr($productt->name, 0,11)."-"}}{{$gs->title}}</title>

	@else

		<meta property="og:title" content="{{$gs->title}}" />
		<meta property="og:image" content="{{asset('assets/images/'.$gs->logo)}}" />
		<meta name="keywords" content="{{ $seo->meta_keys }}">
		<meta name="author" content="NWCMarketplace">
		<title>{{$gs->title}}</title>

	@endif

    <link rel="icon"  type="image/x-icon" href="{{asset('assets/images/'.$gs->favicon)}}"/>
    <!-- Google Font -->
    @if ($default_font->font_value)
		<link href="https://fonts.googleapis.com/css?family={{ $default_font->font_value }}:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap" rel="stylesheet">
	@else
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
	@endif

    <link rel="stylesheet" href="{{ asset('assets/front/css/styles.php?color='.str_replace('#','', $gs->colors).'&header_color='.$gs->header_color) }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/plugin.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/webfonts/flaticon/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/template.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/style.css') }}">
     <link rel="stylesheet" href="{{ asset('assets/front/css/category/default.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/front/css/toastr.min.css') }}">
    @if ($default_font->font_family)
			<link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='.$default_font->font_family) }}">
	@else
			<link rel="stylesheet" id="colorr" href="{{ asset('assets/front/css/font.php?font_familly='."Open Sans") }}">
	@endif

    @if(!empty($seo->google_analytics))
	<script>
		window.dataLayer = window.dataLayer || []; 
		function gtag() {
				dataLayer.push(arguments);
		}
		gtag('js', new Date());
		gtag('config', '{{ $seo->google_analytics }}');
	</script>
	@endif
    @if(!empty($seo->facebook_pixel))
	    <script>
			!function(f,b,e,v,n,t,s)
			{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
			n.callMethod.apply(n,arguments):n.queue.push(arguments)};
			if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
			n.queue=[];t=b.createElement(e);t.async=!0;
			t.src=v;s=b.getElementsByTagName(e)[0];
			s.parentNode.insertBefore(t,s)}(window, document,'script',
			'https://connect.facebook.net/en_US/fbevents.js');
			fbq('init', '{{ $seo->facebook_pixel }}');
			fbq('track', 'PageView');
		</script>
		<noscript>
			<img height="1" width="1" style="display:none"
				 src="https://www.facebook.com/tr?id={{ $seo->facebook_pixel }}&ev=PageView&noscript=1"/>
		</noscript>
	@endif


    @yield('css')
    <style>
        .nw-header {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(1, 12, 24, 0.92);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(16px);
        }

        .nw-header__inner {
            max-width: 1260px;
            margin: 0 auto;
            padding: .85rem clamp(1.25rem, 4vw, 3rem);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nw-brand {
            display: inline-flex;
            align-items: center;
            gap: .75rem;
            text-decoration: none;
        }

        .nw-brand__icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #24befd, #0a7edc);
            color: #f8fbff;
            font-weight: 700;
            font-size: .9rem;
            letter-spacing: .08em;
        }

        .nw-brand__text span {
            display: block;
            font-weight: 600;
            font-size: 1.05rem;
            color: #f8fbff;
            letter-spacing: .02em;
        }

        .nw-brand__text small {
            display: block;
            margin-top: .05rem;
            font-size: .78rem;
            color: rgba(255, 255, 255, 0.58);
            letter-spacing: .08em;
        }

        .nw-nav {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-left: auto;
        }

        .nw-nav a {
            position: relative;
            padding: .45rem .6rem;
            border-radius: 8px;
            font-size: .94rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.72);
            text-decoration: none;
            transition: background .2s ease, color .2s ease;
        }

        .nw-nav a:hover,
        .nw-nav a:focus {
            background: rgba(36, 190, 253, 0.14);
            color: #f8fbff;
        }

        .nw-header__actions {
            display: flex;
            align-items: center;
            gap: .7rem;
            margin-left: 1rem;
        }

        .nw-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: .5rem 1.25rem;
            font-size: .88rem;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid transparent;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .nw-btn--ghost {
            color: rgba(255, 255, 255, 0.78);
            border-color: rgba(255, 255, 255, 0.18);
        }

        .nw-btn--primary {
            color: #051626;
            background: linear-gradient(135deg, #24befd, #0a7edc);
            box-shadow: 0 14px 28px rgba(36, 190, 253, 0.2);
        }

        .nw-btn:hover,
        .nw-btn:focus {
            transform: translateY(-1px);
        }

        .nw-btn--primary:hover,
        .nw-btn--primary:focus {
            box-shadow: 0 16px 32px rgba(36, 190, 253, 0.26);
        }

        .nw-header__toggle {
            display: none;
            background: none;
            border: 0;
            color: #f8fbff;
            font-size: 1.35rem;
            margin-left: auto;
        }

        @media (max-width: 992px) {
            .nw-header__inner {
                flex-wrap: wrap;
                gap: 1rem;
            }

            .nw-nav,
            .nw-header__actions {
                display: none;
                width: 100%;
            }

            .nw-header__toggle {
                display: inline-flex;
            }

            .nw-header.is-open .nw-nav,
            .nw-header.is-open .nw-header__actions {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
                padding-top: .75rem;
            }

            .nw-header.is-open .nw-nav {
                border-top: 1px solid rgba(255, 255, 255, 0.12);
                padding-top: 1rem;
            }

            .nw-header.is-open .nw-header__actions {
                border-top: 1px solid rgba(255, 255, 255, 0.12);
                padding-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div id="page_wrapper" class="bg-white">
        <header class="nw-header" data-header>
            <div class="nw-header__inner">
                <a class="nw-brand" href="{{ url('/') }}" aria-label="{{ $gs->title ?? 'Newworld Market' }}">
                    <span class="nw-brand__icon">NM</span>
                    <span class="nw-brand__text">
                        <span>{{ $gs->title ?? 'Newworld Market' }}</span>
                        <small>{{ __('Cargo Cloud Marketplace') }}</small>
                    </span>
                </a>

                <nav class="nw-nav" aria-label="{{ __('Primary navigation') }}">
                    <a href="{{ url('/') }}">{{ __('Home') }}</a>
                    <a href="{{ url('/catalog') }}">{{ __('Marketplace') }}</a>
                    <a href="{{ url('/contact') }}">{{ __('Contact') }}</a>
                    <a href="{{ url('/order/track') }}">{{ __('Track Order') }}</a>
                    <a href="{{ route('api.documentation') }}">{{ __('API Docs') }}</a>
                </nav>

                <div class="nw-header__actions">
                    @auth
                        <a class="nw-btn nw-btn--ghost" href="{{ url('/dashboard') }}">{{ __('Dashboard') }}</a>
                        <a class="nw-btn nw-btn--primary" href="{{ url('/logout') }}">{{ __('Sign Out') }}</a>
                    @else
                        <a class="nw-btn nw-btn--ghost" href="{{ url('/login') }}">{{ __('Sign In') }}</a>
                        <a class="nw-btn nw-btn--primary" href="{{ url('/register') }}">{{ __('Create Account') }}</a>
                    @endauth
                </div>

                <button class="nw-header__toggle" type="button" aria-label="{{ __('Toggle menu') }}" data-header-toggle>
                    <span class="fas fa-bars"></span>
                </button>
            </div>
        </header>

        <div class="loader">
            <div class="spinner"></div>
        </div>

        @yield('content')



    </div>
    <script>


    var mainurl = "{{ url('/') }}";
    var gs      = {!! json_encode(DB::table('generalsettings')->where('id','=',1)->first(['is_loader','decimal_separator','thousand_separator','is_cookie','is_talkto','talkto'])) !!};
    var ps_category = {{ $ps->category }};

    var lang = {
        'days': '{{ __('Days') }}',
        'hrs': '{{ __('Hrs') }}',
        'min': '{{ __('Min') }}',
        'sec': '{{ __('Sec') }}',
        'cart_already': '{{ __('Already Added To Card.') }}',
        'cart_out': '{{ __('Out Of Stock') }}',
        'cart_success': '{{ __('Successfully Added To Cart.') }}',
        'cart_empty': '{{ __('Cart is empty.') }}',
        'coupon_found': '{{ __('Coupon Found.') }}',
        'no_coupon': '{{ __('No Coupon Found.') }}',
        'already_coupon': '{{ __('Coupon Already Applied.') }}',
        'enter_coupon': '{{ __('Enter Coupon First') }}',
        'minimum_qty_error': '{{ __('Minimum Quantity is:') }}',
        'affiliate_link_copy': '{{ __('Affiliate Link Copied Successfully') }}'
    };

    </script>
     <!-- Include Scripts -->
     <script src="{{ asset('assets/front/js/jquery.min.js') }}"></script>
     <script src="{{ asset('assets/front/js/jquery-ui.min.js') }}"></script>
     <script src="{{ asset('assets/front/js/popper.min.js') }}"></script>
     <script src="{{ asset('assets/front/js/bootstrap.min.js') }}"></script>
     <script src="{{ asset('assets/front/js/plugin.js') }}"></script>
     <script src="{{ asset('assets/front/js/waypoint.js') }}"></script>
     <script src="{{ asset('assets/front/js/owl.carousel.min.js') }}"></script>
     <script src="{{ asset('assets/front/js/wow.js')}}"></script>
     <script type="text/javascript" src="{{asset('assets/front/js/lazy.min.js')}}"></script>
     <script type="text/javascript" src="{{asset('assets/front/js/lazy.plugin.js')}}"></script>
     <script src="{{ asset('assets/front/js/jquery.countdown.js') }}"></script>
     @yield('zoom')
     <script src="{{ asset('assets/front/js/paraxify.js') }}"></script>
     <script src="{{ asset('assets/front/js/toastr.min.js') }}"></script>
     <script src="{{ asset('assets/front/js/custom.js') }}"></script>
     <script src="{{ asset('assets/front/js/main.js') }}"></script>

<script>
    (function initLazyImages() {
        function runLazy() {
            if (!window.jQuery || typeof window.jQuery.fn.Lazy !== 'function') {
                return false;
            }

            window.jQuery('.lazy').Lazy({
                scrollDirection: 'vertical',
                effect: 'fadeIn',
                effectTime: 1000,
                threshold: 0,
                visibleOnly: false,
                onError: function(element) {
                    console.log('error loading ' + element.data('src'));
                }
            });

            return true;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', runLazy);
        } else {
            runLazy();
        }

        if (!runLazy()) {
            var retryInterval = setInterval(function() {
                if (runLazy()) {
                    clearInterval(retryInterval);
                }
            }, 100);
        }
    })();

</script>

<script>
    (function initHeaderToggle() {
        var header = document.querySelector('[data-header]');
        var toggle = document.querySelector('[data-header-toggle]');

        if (!header || !toggle) {
            return;
        }

        toggle.addEventListener('click', function () {
            header.classList.toggle('is-open');
        });
    })();
</script>




     @php
     echo Toastr::message();
     @endphp
     @yield('script')



</body>
</html>
