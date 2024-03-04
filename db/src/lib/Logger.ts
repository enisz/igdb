import Log4js, { Appender, Configuration } from 'log4js';

const isProduction = () => process.argv.includes('-p') || process.argv.includes('-production');

const consoleAppender: Appender = {
    type: 'stdout',
    layout: {
        type: 'pattern',
        pattern: '%d{yyyy-MM-dd hh:mm:ss,SSS} [%[%p%]] (%f{1}:%l:%o) - %m',
    },
}

const fileAppender: Appender = {
    type: 'dateFile',
    filename: 'logs/database.log',
    pattern: '.yyyy-MM-dd',
    compress: true,
    numBackups: 5,
    keepFileExt: true,
    layout: {
        type: 'pattern',
        pattern: '%d{yyyy-MM-dd hh:mm:ss,SSS} [%p] (%f{1}:%l:%o) - %m',
    },
}

const configuration: Configuration = {
    appenders: { consoleAppender, fileAppender },

    categories: {
        default: {
            appenders: ['consoleAppender', 'fileAppender'],
            enableCallStack: true,
            level: isProduction() ? 'info' : 'debug',
        }
    }
};

export default Log4js.configure(configuration);
