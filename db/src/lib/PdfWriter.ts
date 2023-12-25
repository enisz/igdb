import FileWriter from "../abstract/FileWriter";
import Document from "../model/Document";
import Path from 'path';
import Fs from 'fs';

export default class PdfWriter extends FileWriter {
    public extension = 'pdf';
    public async write(document: Document): Promise<void> {
        Fs.writeFileSync(
            Path.join(this.getPath(), `${this.getFilename()}.pdf`),
            'lol',
            { encoding: 'utf-8' }
        );
    }

}
