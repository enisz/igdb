import Mustache from 'mustache';
import Path from 'path';
import { mustachePath } from '..';
import FileHandler from './FileHandler';
import Logger from './Logger';

export default class MustacheHandler {
    protected logger = Logger.getLogger(MustacheHandler.name);
    public static render(template: string, context: Object): string {
        const content = FileHandler.readFile(Path.join(mustachePath, `${template}.mustache`));

        return Mustache.render(content, context);
    }
}
