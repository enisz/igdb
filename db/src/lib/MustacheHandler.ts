import Mustache from 'mustache';
import Path from 'path';
import { mustachePath } from '..';
import FileHandler from './FileHandler';

export default class MustacheHandler {
    public static render(template: string, context: Object): string {
        const content = FileHandler.readFile(Path.join(mustachePath, `${template}.mustache`));

        return Mustache.render(content, context);
    }
}
