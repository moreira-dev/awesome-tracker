/**
 * Namespace for own plugin api calls.
 * We trim the char "/" from both sides and add a leading "/"
 *
 * @type {string}
 */
export const nameSpace = '/' + atGlobal.nameSpace.replace( /^[\/]+|[\/]+$/g, '' );

/**
 * Contains the list of API routes this WordPress has
 * @type {object}
 */
export const apiRoutes = atRoutesGlobal.apiRoutes;

/**
 * Contains the list of saved API routes to track
 * @type {object}
 */
export const currentRoutes = atRoutesGlobal.currentRoutes;

/**
 * Delimiter for string separation
 * @type {string}
 */
export const DELIMITER = atRoutesGlobal.DELIMITER;

/**
 * Contains the settingss values from the DB
 * @type {object}
 */
export const currentSettings = atSettingsGlobal.fields;
