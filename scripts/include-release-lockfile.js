const AdmZip = require( 'adm-zip' );
const { name } = require( '../package.json' );

// wp-scripts uses npm-packlist, which always excludes package-lock.json.
const archivePath = `${ name }.zip`;
const archive = new AdmZip( archivePath );
archive.addLocalFile( 'package-lock.json', name );
archive.writeZip( archivePath );
