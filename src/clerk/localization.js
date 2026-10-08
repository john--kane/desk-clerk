/** Official translations, split into one lazy-loaded asset per language. */
const loaders = new Map( [
	[
		'en-us',
		() =>
			import( '@clerk/localizations/en-US' ).then(
				( locale ) => locale.enUS
			),
	],
	[
		'es-es',
		() =>
			import( '@clerk/localizations/es-ES' ).then(
				( locale ) => locale.esES
			),
	],
	[
		'nl-nl',
		() =>
			import( '@clerk/localizations/nl-NL' ).then(
				( locale ) => locale.nlNL
			),
	],
	[
		'pt-pt',
		() =>
			import( '@clerk/localizations/pt-PT' ).then(
				( locale ) => locale.ptPT
			),
	],
	[
		'ar-sa',
		() =>
			import( '@clerk/localizations/ar-SA' ).then(
				( locale ) => locale.arSA
			),
	],
	[
		'be-by',
		() =>
			import( '@clerk/localizations/be-BY' ).then(
				( locale ) => locale.beBY
			),
	],
	[
		'bg-bg',
		() =>
			import( '@clerk/localizations/bg-BG' ).then(
				( locale ) => locale.bgBG
			),
	],
	[
		'bn-in',
		() =>
			import( '@clerk/localizations/bn-IN' ).then(
				( locale ) => locale.bnIN
			),
	],
	[
		'ca-es',
		() =>
			import( '@clerk/localizations/ca-ES' ).then(
				( locale ) => locale.caES
			),
	],
	[
		'cs-cz',
		() =>
			import( '@clerk/localizations/cs-CZ' ).then(
				( locale ) => locale.csCZ
			),
	],
	[
		'da-dk',
		() =>
			import( '@clerk/localizations/da-DK' ).then(
				( locale ) => locale.daDK
			),
	],
	[
		'de-de',
		() =>
			import( '@clerk/localizations/de-DE' ).then(
				( locale ) => locale.deDE
			),
	],
	[
		'el-gr',
		() =>
			import( '@clerk/localizations/el-GR' ).then(
				( locale ) => locale.elGR
			),
	],
	[
		'en-gb',
		() =>
			import( '@clerk/localizations/en-GB' ).then(
				( locale ) => locale.enGB
			),
	],
	[
		'es-cr',
		() =>
			import( '@clerk/localizations/es-CR' ).then(
				( locale ) => locale.esCR
			),
	],
	[
		'es-mx',
		() =>
			import( '@clerk/localizations/es-MX' ).then(
				( locale ) => locale.esMX
			),
	],
	[
		'es-uy',
		() =>
			import( '@clerk/localizations/es-UY' ).then(
				( locale ) => locale.esUY
			),
	],
	[
		'fa-ir',
		() =>
			import( '@clerk/localizations/fa-IR' ).then(
				( locale ) => locale.faIR
			),
	],
	[
		'fi-fi',
		() =>
			import( '@clerk/localizations/fi-FI' ).then(
				( locale ) => locale.fiFI
			),
	],
	[
		'fr-fr',
		() =>
			import( '@clerk/localizations/fr-FR' ).then(
				( locale ) => locale.frFR
			),
	],
	[
		'he-il',
		() =>
			import( '@clerk/localizations/he-IL' ).then(
				( locale ) => locale.heIL
			),
	],
	[
		'hi-in',
		() =>
			import( '@clerk/localizations/hi-IN' ).then(
				( locale ) => locale.hiIN
			),
	],
	[
		'hr-hr',
		() =>
			import( '@clerk/localizations/hr-HR' ).then(
				( locale ) => locale.hrHR
			),
	],
	[
		'hu-hu',
		() =>
			import( '@clerk/localizations/hu-HU' ).then(
				( locale ) => locale.huHU
			),
	],
	[
		'id-id',
		() =>
			import( '@clerk/localizations/id-ID' ).then(
				( locale ) => locale.idID
			),
	],
	[
		'is-is',
		() =>
			import( '@clerk/localizations/is-IS' ).then(
				( locale ) => locale.isIS
			),
	],
	[
		'it-it',
		() =>
			import( '@clerk/localizations/it-IT' ).then(
				( locale ) => locale.itIT
			),
	],
	[
		'ja-jp',
		() =>
			import( '@clerk/localizations/ja-JP' ).then(
				( locale ) => locale.jaJP
			),
	],
	[
		'kk-kz',
		() =>
			import( '@clerk/localizations/kk-KZ' ).then(
				( locale ) => locale.kkKZ
			),
	],
	[
		'ko-kr',
		() =>
			import( '@clerk/localizations/ko-KR' ).then(
				( locale ) => locale.koKR
			),
	],
	[
		'mn-mn',
		() =>
			import( '@clerk/localizations/mn-MN' ).then(
				( locale ) => locale.mnMN
			),
	],
	[
		'ms-my',
		() =>
			import( '@clerk/localizations/ms-MY' ).then(
				( locale ) => locale.msMY
			),
	],
	[
		'nb-no',
		() =>
			import( '@clerk/localizations/nb-NO' ).then(
				( locale ) => locale.nbNO
			),
	],
	[
		'nl-be',
		() =>
			import( '@clerk/localizations/nl-BE' ).then(
				( locale ) => locale.nlBE
			),
	],
	[
		'pl-pl',
		() =>
			import( '@clerk/localizations/pl-PL' ).then(
				( locale ) => locale.plPL
			),
	],
	[
		'pt-br',
		() =>
			import( '@clerk/localizations/pt-BR' ).then(
				( locale ) => locale.ptBR
			),
	],
	[
		'ro-ro',
		() =>
			import( '@clerk/localizations/ro-RO' ).then(
				( locale ) => locale.roRO
			),
	],
	[
		'ru-ru',
		() =>
			import( '@clerk/localizations/ru-RU' ).then(
				( locale ) => locale.ruRU
			),
	],
	[
		'sk-sk',
		() =>
			import( '@clerk/localizations/sk-SK' ).then(
				( locale ) => locale.skSK
			),
	],
	[
		'sr-rs',
		() =>
			import( '@clerk/localizations/sr-RS' ).then(
				( locale ) => locale.srRS
			),
	],
	[
		'sv-se',
		() =>
			import( '@clerk/localizations/sv-SE' ).then(
				( locale ) => locale.svSE
			),
	],
	[
		'ta-in',
		() =>
			import( '@clerk/localizations/ta-IN' ).then(
				( locale ) => locale.taIN
			),
	],
	[
		'te-in',
		() =>
			import( '@clerk/localizations/te-IN' ).then(
				( locale ) => locale.teIN
			),
	],
	[
		'th-th',
		() =>
			import( '@clerk/localizations/th-TH' ).then(
				( locale ) => locale.thTH
			),
	],
	[
		'tr-tr',
		() =>
			import( '@clerk/localizations/tr-TR' ).then(
				( locale ) => locale.trTR
			),
	],
	[
		'uk-ua',
		() =>
			import( '@clerk/localizations/uk-UA' ).then(
				( locale ) => locale.ukUA
			),
	],
	[
		'vi-vn',
		() =>
			import( '@clerk/localizations/vi-VN' ).then(
				( locale ) => locale.viVN
			),
	],
	[
		'zh-cn',
		() =>
			import( '@clerk/localizations/zh-CN' ).then(
				( locale ) => locale.zhCN
			),
	],
	[
		'zh-tw',
		() =>
			import( '@clerk/localizations/zh-TW' ).then(
				( locale ) => locale.zhTW
			),
	],
] );

/**
 * Resolve WordPress region/variant codes without loading unrelated translations.
 *
 * @param {string|undefined} locale WordPress locale; omitted when disabled.
 */
export async function loadLocalization( locale ) {
	if ( typeof locale !== 'string' ) {
		return undefined;
	}
	const normalized = locale.replaceAll( '_', '-' ).toLowerCase();
	if ( ! /^[a-z]{2,3}(?:-[a-z0-9]{2,8})*$/.test( normalized ) ) {
		return undefined;
	}
	const parts = normalized.split( '-' );
	let tag = parts.slice( 0, 2 ).join( '-' );
	if (
		parts[ 0 ] === 'zh' &&
		( parts.includes( 'hant' ) ||
			parts.includes( 'hk' ) ||
			parts.includes( 'mo' ) )
	) {
		tag = 'zh-tw';
	} else if ( parts[ 0 ] === 'bel' ) {
		tag = 'be-by';
	} else if ( parts[ 0 ] === 'no' ) {
		tag = 'nb-no';
	}
	const load =
		loaders.get( tag ) ||
		Array.from( loaders ).find(
			( [ supported ] ) => supported.split( '-' )[ 0 ] === parts[ 0 ]
		)?.[ 1 ];
	if ( ! load ) {
		return undefined;
	}
	try {
		return await load();
	} catch ( error ) {
		// Experimental translation loading must not prevent authentication.
		window.console.warn(
			'Clerk localization could not be loaded; using default English.',
			error
		);
		return undefined;
	}
}
