(() => {
	// setup some constants
	const API_BASE = '/api/deepl/translate';
	const FIELD_BUTTON_SELECTOR = '[data-translate-field]';
	const ALL_BUTTON_SELECTOR = '[data-translate-all]';
	const LOADER_CLASS = 'translate-loader';
	const SHOW_LOADER_CLASS = 'show--translate-loader';

	// assume source language is de
	let sourceLang = 'de';

	document.addEventListener('DOMContentLoaded', e => {
		// add loader element
		const loader = document.createElement('div');
		loader.classList.add(LOADER_CLASS);
		document.body.appendChild(loader);

		// add loader styles
		const loaderStyles = document.createElement('style');
		loaderStyles.innerHTML = `
			.${LOADER_CLASS} {
				background: rgba(0,0,0,0.8);
				display: none;
				flex-flow: column;
				align-items: center;
				align-content: center;
				justify-content: center;
				position: fixed;
				inset: 0;
				z-index: 99999999999999999;
			}

			.${SHOW_LOADER_CLASS} .${LOADER_CLASS} {
				display: flex;
			}

			.${LOADER_CLASS}:before {
				animation: translate-loader-spinner 1s linear infinite;
				border: 0.25rem solid rgba(244, 122, 0, 0.25);
				border-top-color: #f47c00;
				border-radius: 50%;
				display: block;
				content: '';
				width: 2.5rem;
				height: 2.5rem;
			}

			.${LOADER_CLASS}:after {
				color: #fff;
				content: 'translating';
				margin-top: 0.5rem;
			}

			@keyframes translate-loader-spinner {
				0% {
					transform: rotate(0deg);
				}
				100% {
					transform: rotate(360deg);
				}
			}
		`;
		document.head.appendChild(loaderStyles);

		// add global click handler
		document.addEventListener('click', e => {
			let target = e.target;
			let button = target;

			// check target selector / parents
			let isFieldButton = target.matches(FIELD_BUTTON_SELECTOR);
			let isAllButton = target.matches(ALL_BUTTON_SELECTOR);
			let closestFieldButton = target.closest(FIELD_BUTTON_SELECTOR);
			let closestAllButton = target.closest(ALL_BUTTON_SELECTOR);

			// if target is child of field button
			if (!isFieldButton && closestFieldButton) {
				button = closestFieldButton;
				isFieldButton = true;
			}
			// if target is child of all button
			else if (!isAllButton && closestAllButton) {
				button = closestAllButton;
				isAllButton = true;
			}

			// translate field from field button
			if (isFieldButton) {
				e.preventDefault();
				const fieldName = button.dataset.translateField;
				const targetLang = button.dataset.translateTargetLang;
				translateFields([fieldName], targetLang);
			}

			// translate all fields from all button
			if (isAllButton) {
				e.preventDefault();
				const fields = Array.from(document.querySelectorAll('[data-translate-field]'));
				const fieldNames = fields.map(field => field.dataset.translateField);
				const targetLang = button.dataset.translateTargetLang;
				translateFields(fieldNames, targetLang);
			}
		});
	});

	async function translateFields(fieldNames = [], targetLang = sourceLang) {
		// get fields
		const fields = fieldNames.reduce((arr, name) => {
			const field = document.querySelector(`[name="${name}"]`);
			if (field) {
				arr.push(field);
			}
			return arr;
		}, []);


		if (fields.length) {
			// dynamically ask for the source language, with default of de
			// const lang = prompt('Übersetzen von? [de|en|fr|it]', sourceLang);
			// sourceLang = lang || sourceLang;

			// show loader
			document.documentElement.classList.add(SHOW_LOADER_CLASS);

			// prepare data
			const data = {
				texts: fields.map(field => {
					// get the value from the html form input
					let value = field.value;

					if (window.tinyMCE) {
						// get tinyMCE instance
						const wysiwyg = tinyMCE.get(field.id);

						if (wysiwyg) {
							// get content from tinyMCE instance
							value = wysiwyg.getContent();
						}
					}

					return value;
				}),
				sourceLang,
				targetLang
			}

			// send get request
			const response = await fetch(`${API_BASE}?${urlParams(data)}`);
			const results = await response.json();

			if (results.translations) {
				// loop through translations
				results.translations.forEach((t, i) => {
					// assign translation to field
					fields[i].value = t.translation;

					// check if tinyMCE exists
					if (window.tinyMCE) {
						// get tinyMCE instance
						const wysiwyg = tinyMCE.get(fields[i].id);

						if (wysiwyg) {
							// set content for tinyMCE instance
							wysiwyg.setContent(t.translation);
						}
					}
				});
			}

			// hide loader
			document.documentElement.classList.remove(SHOW_LOADER_CLASS);
		}
	}

	// function to create url param string
	function urlParams(obj, prefix) {
		if (obj instanceof FormData) {
			const temp = {};

			obj.forEach(function(value, key) {
				temp[key] = value;
			});

			obj = temp;
		}
		else if (!/^o/.test(typeof obj)) {
			return obj;
		}

		const str = [];
		for (const p in obj) {
			if (obj.hasOwnProperty(p) && obj[p] !== '') {
				const k = prefix ? prefix + '[' + p + ']' : p;
				const v = obj[p];
				str.push(typeof v === 'object' ? urlParams(v, k) : encodeURIComponent(k) + '=' + encodeURIComponent(v));
			}
		}
		return str.join('&');
	}
})()
