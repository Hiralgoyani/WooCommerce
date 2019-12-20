<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'basicwoo' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Y|A:yIBB;ZyP~RhXM{n=y];Q[STi|u# *F*>Q@}HQ!c|^B=Gd Bwy{xmC4]8S<4,' );
define( 'SECURE_AUTH_KEY',  '6w$?_A0bUhlh&}X_![AMe}TsyysL&11o}mTn>-&j dt<VB}fN9AM.r!K#ZER,!/M' );
define( 'LOGGED_IN_KEY',    'dRm_X@Lxcw3@3%3nycazh7=JZfhEpfHyn;E}jtLx5RTj]#qJ-v_@7L*_xd!q`<*_' );
define( 'NONCE_KEY',        '$2:feIG*$:%/$RgaN]h(dmV_rR~,Sx=aN.~Y;^~_N%kIMBxgS_vCiEQE)ZLM1-!t' );
define( 'AUTH_SALT',        'ykURw%V7UkV,.NDxJ1,G=4[c@~U]whB^G,[qPlzl$qM>8O%gRsV&zuw2%ZD-hUyB' );
define( 'SECURE_AUTH_SALT', '$HsZsoke&Gz#9Py|?g(BXCyzO.=y=|?$Mkn8$jJ YAf91FrPQ[7DM1D}Jdomm Pq' );
define( 'LOGGED_IN_SALT',   '{lv8}lDeb{[!.=9[X%fI.ul;x[,06`;z6bnKV>,)o9zG?G# E[_GG+t=g%$@6}C-' );
define( 'NONCE_SALT',       '<#o)R6(Gx24z->EL0zP:xml}=uJd:lWUldFdi~qu_f@qNIBpXhg}7Q8130%%0@$;' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
