(() => {
	// setup some constants
	const API_BASE = '/api/deepl/translate';
	const FIELD_BUTTON_SELECTOR = '[data-translate-field]';
	const MULTI_COLUMN_BUTTON_SELECTOR = '[data-translate-multicol]';
	const ALL_BUTTON_SELECTOR = '[data-translate-all]';
	const LOADER_CLASS = 'translate-loader';
	const SHOW_LOADER_CLASS = 'show--translate-loader';

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
			let isMultiColumnButton = target.matches(MULTI_COLUMN_BUTTON_SELECTOR);
			let isAllButton = target.matches(ALL_BUTTON_SELECTOR);
			let closestFieldButton = target.closest(FIELD_BUTTON_SELECTOR);
			let closestMutiColumnButton = target.closest(MULTI_COLUMN_BUTTON_SELECTOR);
			let closestAllButton = target.closest(ALL_BUTTON_SELECTOR);

			// if target is child of field button
			if (!isFieldButton && closestFieldButton) {
				button = closestFieldButton;
				isFieldButton = true;
			}
			// if target is child of multi column button
			else if (!isMultiColumnButton && closestMutiColumnButton) {
				button = closestMutiColumnButton;
				isMultiColumnButton = true;
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
				const sourceLang = button.dataset.translateSourceLang;
				const targetLang = button.dataset.translateTargetLang;
				translateFields([fieldName], sourceLang, targetLang);
			}

			if (isMultiColumnButton) {
				e.preventDefault();
				const fieldNames = getMultiColumnFields(button);
                const sourceLang = button.dataset.translateSourceLang;
                const targetLang = button.dataset.translateTargetLang;
				translateFields(fieldNames, sourceLang, targetLang);
			}

			// translate all fields from all button
			if (isAllButton) {
				e.preventDefault();
				const fields = Array.from(document.querySelectorAll(FIELD_BUTTON_SELECTOR));
				const multiColumns = Array.from(document.querySelectorAll(MULTI_COLUMN_BUTTON_SELECTOR));
				const fieldNames = [
					...fields.map(field => field.dataset.translateField),
					...multiColumns.reduce((fields, button) => {
						return [
							...fields,
							...getMultiColumnFields(button),
						];
					}, [])
				];
                const sourceLang = button.dataset.translateSourceLang;
                const targetLang = button.dataset.translateTargetLang;
				translateFields(fieldNames, sourceLang, targetLang);
			}
		});
	});

	function getMultiColumnFields(button) {
		const multiColumnName = button.dataset.translateMulticol;
		const widget = button.closest('.widget');
		const rowIds = Array.from(widget.querySelectorAll('table tbody tr')).map(row => row.dataset.rowid);
		const fieldNames = button.dataset.translateFields.split(/,\s?/).map(name => {
			return rowIds.map(row => {
				return `${multiColumnName}[${row}][${name}]`
			})
		}).flat();
		return fieldNames;
	}

	async function translateFields(fieldNames = [], sourceLang, targetLang) {
		// get fields
		const fields = fieldNames.reduce((arr, name) => {
			const element = document.querySelector(`[name="${name}"]`);
			if (element) {
				arr.push({
					name,
					element,
				});
			}
			return arr;
		}, []);

		if (fields.length) {
			// show loader
			document.documentElement.classList.add(SHOW_LOADER_CLASS);

			// prepare data
			const data = {
				texts: fields.reduce((texts, field) => {
					// get the value from the html form input
					let value = field.element.value;

					if (window.tinyMCE) {
						// get tinyMCE instance
						const wysiwyg = tinyMCE.get(field.element.id);

						if (wysiwyg) {
							// get content from tinyMCE instance
							value = wysiwyg.getContent();
						}
					}

					// if value is not empty, add value with field name as key
					if (value) {
						texts[field.name] = value;
					}

					return texts;
				}, {}),
				sourceLang,
				targetLang
			}


			// send get request
			const response = await fetch(`${API_BASE}?${urlParams(data)}`);
			const results = await response.json();

			if (results.translations) {
				// loop through translations
				Object.entries(results.translations).forEach(([key, value]) => {
					// find field in fields array
					const field = fields.find(field => field.name === key)

					// check if field exists
					if (field) {
						// assign translation to field
						field.element.value = value.translation;

						// check if tinyMCE exists
						if (window.tinyMCE) {
							// get tinyMCE instance
							const wysiwyg = tinyMCE.get(field.element.id);

							if (wysiwyg) {
								// set content for tinyMCE instance
								wysiwyg.setContent(value.translation);
							}
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

			obj.forEach(function (value, key) {
				temp[key] = value;
			});

			obj = temp;
		} else if (!/^o/.test(typeof obj)) {
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
