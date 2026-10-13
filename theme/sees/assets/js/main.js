/**
 * SEES theme scripts — no dependencies.
 */
( function () {
	'use strict';

	var reduceMotion = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	/* ---------- Scroll reveal ---------- */
	var revealEls = document.querySelectorAll( '.reveal' );
	if ( 'IntersectionObserver' in window && ! reduceMotion ) {
		var io = new IntersectionObserver( function ( entries ) {
			entries.forEach( function ( entry ) {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'is-visible' );
					io.unobserve( entry.target );
				}
			} );
		}, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 } );
		revealEls.forEach( function ( el ) { io.observe( el ); } );
	} else {
		revealEls.forEach( function ( el ) { el.classList.add( 'is-visible' ); } );
	}

	/* ---------- Header circuit: pause the current animation while off-screen ---------- */
	var circuit = document.querySelector( '.page-header__decor' );
	if ( circuit && 'IntersectionObserver' in window ) {
		new IntersectionObserver( function ( entries ) {
			circuit.classList.toggle( 'is-paused', ! entries[ 0 ].isIntersecting );
		} ).observe( circuit );
	}

	/* ---------- Light / dark theme ---------- */
	var root = document.documentElement;
	var themeButtons = document.querySelectorAll( '.theme-toggle' );
	var darkQuery = window.matchMedia ? window.matchMedia( '(prefers-color-scheme: dark)' ) : null;

	function savedTheme() {
		try {
			return localStorage.getItem( 'sees-theme' );
		} catch ( e ) {
			return null;
		}
	}

	function applyTheme( theme ) {
		root.setAttribute( 'data-theme', theme );
		themeButtons.forEach( function ( btn ) {
			btn.setAttribute( 'aria-pressed', theme === 'dark' ? 'true' : 'false' );
		} );
	}

	applyTheme( root.getAttribute( 'data-theme' ) || 'light' );

	themeButtons.forEach( function ( btn ) {
		btn.addEventListener( 'click', function () {
			var next = root.getAttribute( 'data-theme' ) === 'dark' ? 'light' : 'dark';
			applyTheme( next );
			try {
				localStorage.setItem( 'sees-theme', next );
			} catch ( e ) {}
		} );
	} );

	// Follow system changes until the visitor picks a theme.
	if ( darkQuery && darkQuery.addEventListener ) {
		darkQuery.addEventListener( 'change', function ( e ) {
			if ( ! savedTheme() ) {
				applyTheme( e.matches ? 'dark' : 'light' );
			}
		} );
	}

	/* ---------- Mobile navigation ---------- */
	var header = document.getElementById( 'site-header' );
	var navToggle = document.querySelector( '.nav-toggle' );
	var nav = document.getElementById( 'primary-nav' );

	function setNav( open ) {
		if ( ! nav || ! navToggle ) {
			return;
		}
		nav.classList.toggle( 'is-open', open );
		header.classList.toggle( 'nav-open', open );
		navToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	}

	if ( navToggle ) {
		navToggle.addEventListener( 'click', function () {
			setNav( navToggle.getAttribute( 'aria-expanded' ) !== 'true' );
		} );
	}

	/* ---------- Search panel ---------- */
	var searchToggle = document.querySelector( '.search-toggle' );
	var searchPanel = document.getElementById( 'site-search' );

	function setSearch( open ) {
		if ( ! searchPanel || ! searchToggle ) {
			return;
		}
		searchPanel.hidden = ! open;
		searchToggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		if ( open ) {
			var input = searchPanel.querySelector( 'input[type="search"]' );
			if ( input ) {
				input.focus();
			}
		}
	}

	if ( searchToggle ) {
		searchToggle.addEventListener( 'click', function () {
			setSearch( searchPanel.hidden );
		} );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Escape' ) {
			return;
		}
		if ( searchPanel && ! searchPanel.hidden ) {
			setSearch( false );
			searchToggle.focus();
		}
		if ( nav && nav.classList.contains( 'is-open' ) ) {
			setNav( false );
			navToggle.focus();
		}
	} );

	/* ---------- Projects filter ---------- */
	var filters = document.querySelector( '[data-filters]' );
	var grid = document.querySelector( '[data-projects]' );

	if ( filters && grid ) {
		var cards = Array.prototype.slice.call( grid.querySelectorAll( '.project-card' ) );
		var empty = document.querySelector( '[data-projects-empty]' );
		var pills = Array.prototype.slice.call( filters.querySelectorAll( '[data-filter]' ) );

		var applyFilter = function ( slug ) {
			var shown = 0;
			pills.forEach( function ( pill ) {
				if ( pill.getAttribute( 'data-filter' ) === slug ) {
					pill.setAttribute( 'aria-current', 'true' );
				} else {
					pill.removeAttribute( 'aria-current' );
				}
			} );
			cards.forEach( function ( card ) {
				var cats = ( card.getAttribute( 'data-cats' ) || '' ).split( ' ' );
				var match = ! slug || cats.indexOf( slug ) !== -1;
				card.hidden = ! match;
				card.classList.remove( 'is-entering' );
				if ( match ) {
					shown++;
					if ( ! reduceMotion ) {
						void card.offsetWidth; // Restart the animation.
						card.classList.add( 'is-entering' );
					}
				}
			} );
			if ( empty ) {
				empty.hidden = shown > 0;
			}
		};

		pills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function ( e ) {
				// The archive may be paginated or a category archive; only filter in place on the main archive.
				if ( document.body.classList.contains( 'tax-project_category' ) ) {
					return;
				}
				e.preventDefault();
				var slug = pill.getAttribute( 'data-filter' );
				applyFilter( slug );
				if ( window.history && history.replaceState ) {
					var url = new URL( window.location.href );
					if ( slug ) {
						url.searchParams.set( 'cat', slug );
					} else {
						url.searchParams.delete( 'cat' );
					}
					history.replaceState( null, '', url.toString() );
				}
			} );
		} );
	}

	/* ---------- Jobs filters ---------- */
	var jobFilters = document.querySelector( '[data-job-filters]' );
	var jobsGrid = document.querySelector( '[data-job-grid]' );

	if ( jobFilters && jobsGrid ) {
		var jobSelects = Array.prototype.slice.call( jobFilters.querySelectorAll( '[data-job-filter]' ) );
		var jobCards = Array.prototype.slice.call( jobsGrid.querySelectorAll( '[data-job-card]' ) );
		var jobEmpty = document.querySelector( '[data-job-filter-empty]' );

		var filterJobs = function () {
			var shown = 0;
			jobCards.forEach( function ( card ) {
				var matches = jobSelects.every( function ( select ) {
					var wanted = select.value;
					return ! wanted || card.getAttribute( 'data-' + select.getAttribute( 'data-job-filter' ) ) === wanted;
				} );
				card.hidden = ! matches;
				if ( matches ) {
					shown++;
				}
			} );
			if ( jobEmpty ) {
				jobEmpty.hidden = shown > 0;
			}
		};

		jobSelects.forEach( function ( select ) {
			select.addEventListener( 'change', filterJobs );
		} );
		jobFilters.addEventListener( 'reset', function () {
			window.setTimeout( filterJobs, 0 );
		} );
	}

	/* ---------- Support downloads: search + type + language filters in place ---------- */
	var downloads = document.querySelector( '[data-downloads]' );
	if ( downloads && downloads.querySelector( '[data-downloads-list]' ) ) {
		var dlItems = Array.prototype.slice.call( downloads.querySelectorAll( '[data-download]' ) );
		var dlQuery = downloads.querySelector( '[data-downloads-q]' );
		var dlLang = downloads.querySelector( '[data-downloads-lang]' );
		var dlPills = Array.prototype.slice.call( downloads.querySelectorAll( '[data-downloads-type]' ) );
		var dlCount = downloads.querySelector( '[data-downloads-count]' );
		var dlEmpty = downloads.querySelector( '[data-downloads-empty]' );
		var dlTypeInput = downloads.querySelector( '[data-downloads-type-input]' );
		var dlType = dlTypeInput ? dlTypeInput.value : '';

		var filterDownloads = function () {
			var words = ( dlQuery ? dlQuery.value : '' ).toLowerCase().split( /\s+/ ).filter( Boolean );
			var lang = dlLang ? dlLang.value : '';
			var shown = 0;
			dlItems.forEach( function ( item ) {
				var types = ( item.getAttribute( 'data-types' ) || '' ).split( ' ' );
				var itemLang = item.getAttribute( 'data-lang' );
				var text = item.getAttribute( 'data-search' ) || '';
				var match = ( ! dlType || types.indexOf( dlType ) !== -1 ) &&
					( ! lang || itemLang === 'both' || itemLang === lang ) &&
					words.every( function ( w ) { return text.indexOf( w ) !== -1; } );
				item.hidden = ! match;
				if ( match ) {
					shown++;
				}
			} );
			if ( dlCount ) {
				dlCount.textContent = shown === 1 ? dlCount.getAttribute( 'data-one' ) : dlCount.getAttribute( 'data-many' ).replace( '%s', shown );
			}
			if ( dlEmpty ) {
				dlEmpty.hidden = shown > 0;
			}
			dlPills.forEach( function ( pill ) {
				if ( pill.getAttribute( 'data-downloads-type' ) === dlType ) {
					pill.setAttribute( 'aria-current', 'true' );
				} else {
					pill.removeAttribute( 'aria-current' );
				}
			} );
			if ( window.history && history.replaceState ) {
				var url = new URL( window.location.href );
				[ [ 'q', dlQuery ? dlQuery.value.trim() : '' ], [ 'type', dlType ], [ 'doc_lang', lang ] ].forEach( function ( pair ) {
					if ( pair[ 1 ] ) {
						url.searchParams.set( pair[ 0 ], pair[ 1 ] );
					} else {
						url.searchParams.delete( pair[ 0 ] );
					}
				} );
				history.replaceState( null, '', url.toString() );
			}
		};

		if ( dlQuery ) {
			dlQuery.addEventListener( 'input', filterDownloads );
		}
		if ( dlLang ) {
			dlLang.addEventListener( 'change', filterDownloads );
		}
		dlPills.forEach( function ( pill ) {
			pill.addEventListener( 'click', function ( e ) {
				e.preventDefault();
				dlType = pill.getAttribute( 'data-downloads-type' );
				filterDownloads();
			} );
		} );
		var dlForm = downloads.querySelector( '[data-downloads-form]' );
		if ( dlForm ) {
			dlForm.addEventListener( 'submit', function ( e ) {
				e.preventDefault();
				filterDownloads();
			} );
		}
		filterDownloads();
	}

	/* ---------- FAQ: one open at a time (fallback for browsers without <details name>) ---------- */
	var faqItems = document.querySelectorAll( '.faq__item' );
	faqItems.forEach( function ( item ) {
		item.addEventListener( 'toggle', function () {
			if ( item.open ) {
				faqItems.forEach( function ( other ) {
					if ( other !== item ) {
						other.open = false;
					}
				} );
			}
		} );
	} );

	/* ---------- Contact form success panel ---------- */
	var formWrap = document.querySelector( '[data-contact-form]' );
	if ( formWrap ) {
		var panel = formWrap.querySelector( '[data-form-panel]' );
		var success = formWrap.querySelector( '[data-form-success]' );
		var reset = formWrap.querySelector( '[data-form-reset]' );

		formWrap.addEventListener( 'wpcf7mailsent', function () {
			panel.hidden = true;
			success.hidden = false;
			success.focus();
		} );

		if ( reset ) {
			reset.addEventListener( 'click', function () {
				success.hidden = true;
				panel.hidden = false;
				var first = panel.querySelector( 'input:not([type="hidden"]), textarea, select' );
				if ( first ) {
					first.focus();
				}
			} );
		}
	}
}() );
