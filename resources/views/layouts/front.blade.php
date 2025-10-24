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
        .nwcm-header {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: rgba(11, 31, 51, 0.96);
            backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .nwcm-header__inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem clamp(1.5rem, 4vw, 3.5rem);
            gap: 1.5rem;
        }

        .nwcm-brand {
            display: inline-flex;
            gap: .75rem;
            align-items: center;
            text-decoration: none;
        }

        .nwcm-brand__icon {
            display: grid;
            place-items: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #31c6f4, #0b85c4);
            color: #f6fbff;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .nwcm-brand__text span {
            display: block;
            font-weight: 600;
            color: #f6fbff;
            font-size: 1.1rem;
        }

        .nwcm-brand__text small {
            display: block;
            margin-top: .15rem;
            font-size: .8rem;
            color: rgba(255, 255, 255, 0.58);
            letter-spacing: .04em;
        }

        .nwcm-nav {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .nwcm-nav a {
            position: relative;
            font-size: .95rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.72);
            text-decoration: none;
            padding-bottom: .15rem;
        }

        .nwcm-nav a::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -4px;
            width: 100%;
            height: 2px;
            background: #31c6f4;
            transform: scaleX(0);
            transform-origin: center;
            transition: transform .2s ease;
        }

        .nwcm-nav a:hover,
        .nwcm-nav a:focus {
            color: #f6fbff;
        }

        .nwcm-nav a:hover::after,
        .nwcm-nav a:focus::after {
            transform: scaleX(1);
        }

        .nwcm-header__actions {
            display: flex;
            gap: .75rem;
            align-items: center;
        }

        .nwcm-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            padding: .55rem 1.4rem;
            font-weight: 600;
            font-size: .9rem;
            border: 1px solid transparent;
            text-decoration: none;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .nwcm-btn--secondary {
            color: rgba(255, 255, 255, 0.78);
            border-color: rgba(255, 255, 255, 0.16);
        }

        .nwcm-btn--primary {
            color: #0b1f33;
            background: #31c6f4;
            box-shadow: 0 10px 25px rgba(49, 198, 244, 0.25);
        }

        .nwcm-btn:hover,
        .nwcm-btn:focus {
            transform: translateY(-1px);
        }

        .nwcm-btn--primary:hover,
        .nwcm-btn--primary:focus {
            box-shadow: 0 14px 28px rgba(49, 198, 244, 0.28);
        }

        .nwcm-header__toggle {
            display: none;
            background: none;
            border: none;
            color: #f6fbff;
            font-size: 1.5rem;
        }

        @media (max-width: 992px) {
            .nwcm-nav,
            .nwcm-header__actions {
                display: none;
            }

            .nwcm-header__toggle {
                display: inline-flex;
            }

            .nwcm-header.is-open .nwcm-nav,
            .nwcm-header.is-open .nwcm-header__actions {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
                padding: 1rem clamp(1.5rem, 4vw, 3.5rem) 1.5rem;
                gap: 1rem;
            }

            .nwcm-header.is-open .nwcm-nav {
                border-top: 1px solid rgba(255, 255, 255, 0.12);
                padding-top: 1.25rem;
            }

            .nwcm-header.is-open .nwcm-nav a::after {
                display: none;
            }

            .nwcm-header.is-open .nwcm-header__actions {
                border-top: 1px solid rgba(255, 255, 255, 0.12);
                padding-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <div id="page_wrapper" class="bg-white">
        <header class="nwcm-header" data-header>
            <div class="nwcm-header__inner">
                <a class="nwcm-brand" href="{{ url('/') }}">
                    <span class="nwcm-brand__icon">NW</span>
                    <span class="nwcm-brand__text">
                        <span>{{ $gs->title ?? 'Cargo Marketplace' }}</span>
                        <small>Cloud Commerce Platform</small>
                    </span>
                </a>

                <nav class="nwcm-nav" aria-label="Primary">
                    <a href="{{ url('/') }}">{{ __('Home') }}</a>
                    <a href="{{ url('/catalog') }}">{{ __('Catalog') }}</a>
                    <a href="{{ url('/contact') }}">{{ __('Contact') }}</a>
                    <a href="{{ url('/track-order') }}">{{ __('Track Order') }}</a>
                </nav>

                <div class="nwcm-header__actions">
                    @auth
                        <a class="nwcm-btn nwcm-btn--secondary" href="{{ url('/dashboard') }}">{{ __('Dashboard') }}</a>
                        <a class="nwcm-btn nwcm-btn--primary" href="{{ url('/logout') }}">{{ __('Sign Out') }}</a>
                    @else
                        <a class="nwcm-btn nwcm-btn--secondary" href="{{ url('/login') }}">{{ __('Sign In') }}</a>
                        <a class="nwcm-btn nwcm-btn--primary" href="{{ url('/register') }}">{{ __('Get Started') }}</a>
                    @endauth
                </div>

                <button class="nwcm-header__toggle" type="button" aria-label="{{ __('Toggle navigation') }}" data-header-toggle>
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
