export const CLIENT_ID_REGEXP = new RegExp('\{client_id\}', 'gi');
export const ACCESS_TOKEN_REGEXP = new RegExp('\{access_token\}', 'gi');
export const TOKEN_VALIDATOR_PATTERN = '^((?=.+[a-z])(?=.+[0-9])[a-z0-9]{30})$';
