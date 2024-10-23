<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '$<(Z$49(S8~MpIdQA^!uBiS9ncS}#H3+~@(+soowSj6Q6T4[/KRS_6O<3vk3zb1^' );
define( 'SECURE_AUTH_KEY',   'Bo]wtTu/*gOnlj5ru1ax`y_R?AX:;*n8uT+}yWLk:WdoDnd0V[fbq7)shIS@|f4a' );
define( 'LOGGED_IN_KEY',     'i=^`=:J={#A|h9|x&@S.o7w*A5)gQ1$N#Y%.`:KAaf&o2?.Z@Gx|F|86]zKCfFAz' );
define( 'NONCE_KEY',         'wHVIS{UB1#Gx!2?YAIPP1m>Rf~ DlQ`1<B73*DVIjx lYMLX:>tG1R>hCaWn_n@s' );
define( 'AUTH_SALT',         '6]y-/WyH!n<Zhj|u!8JnS=rNBT2.aC01O^|$iFuFn)kOSH}TE/u/1c26UTpIFMwc' );
define( 'SECURE_AUTH_SALT',  '||%PVI=Uzc4RTkW-#-;kB<72zP{{QJymQ}P,oHT^G7rJIq>]Pmxj068-I<fl,ZW0' );
define( 'LOGGED_IN_SALT',    'W/h0wIv1<nA!YjYxnTX:eTUqS_Pnv#I,Yo}G}B7jCtb/QlJOsXzVchybsh, K,4N' );
define( 'NONCE_SALT',        '1e 3JLYZ?CYz+MWkX,^mK>4bE>Cl8mU/]d/N~]Wv${(D,&wTP0&vXVo.g/y%pu?Q' );
define( 'WP_CACHE_KEY_SALT', 'uh!i{Z @^Nf[1,LV>~D9oZN)HNz&`>9U(_qNjsntLX(j]2q@h7Y{3.]!LVz/o?B9' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
