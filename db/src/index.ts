import { Option, program } from 'commander';
import Fs from 'fs';
import Path from 'path';
import FileWriter from "./abstract/FileWriter";
import Builder from "./lib/Builder";
import Logger from './lib/Logger';
import Processor from "./lib/Processor";
import Reader from "./lib/Reader";
import RxdbWriter from "./lib/RxdbWriter";

program
    .addOption(new Option('-p, --production', 'Generate production ready database file').default(false))
    .addOption(new Option('-f, --filename <filename>', 'Override output filename'))
    .addOption(new Option('-w, --watch', 'Executing the script in dev mode, restart when changes detected').default(false))
    .addOption(new Option('-o, --output <format>', 'Output of the script').choices(['rxdb']).default('rxdb'));
program.parse(process.argv);

const { output, watch, filename } = program.opts();
export const templatePath = Path.join(__dirname, '..', 'assets', 'templates');
export const mustachePath = Path.join(__dirname, '..', 'assets', 'mustache');
export const exportPath = Path.join(__dirname, '..', '..', 'src', 'assets');
export const databaseName = filename || 'database';
const logger = Logger.getLogger(__filename);
const reader = new Reader(templatePath);
const processor = new Processor();
let writer: FileWriter;

logger.info('Building database');
logger.debug({ output, watch, filename, templatePath, mustachePath, exportPath, databaseName });

switch(output) {
    case 'rxdb':
        writer = new RxdbWriter(exportPath, databaseName);
        break;
    default:
        throw new Error(`Invalid output format: ${output}!`);
}
const builder = new Builder(reader, processor, writer);
builder.build();

if(watch) {
    console.log(`\nWatching for changes in ${templatePath}\n`);
    let fsWait: NodeJS.Timeout | null = null;
    Fs.watch(templatePath, (event, filename) => {
        if (filename) {
            if (fsWait) return;

            fsWait = setTimeout(() => {
                fsWait = null;
            }, 100);

            console.log(`${Path.join(templatePath, filename)} ${event}d!`);
            builder.build();
        }
    });
}
