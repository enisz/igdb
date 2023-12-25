import FileReader from '../abstract/FileReader'
import File from '../model/File';
import Fs from 'fs';
import Path from 'path';

export default class Reader extends FileReader {
    public constructor(path: string) {
        super(path);
    }

    public read(): File[] {
        return Fs
            .readdirSync(this.getPath(), { encoding: 'utf-8'})
            .filter((filename: string) => filename.endsWith('.md'))
            .map((filename: string) =>
                new File(this.getPath(), filename, Fs.readFileSync(Path.join(this.getPath(), filename), { encoding: 'utf-8'})
            ));
    }
}
