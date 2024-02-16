import Section from "./Section";
import Topic from "./Topic";

export default class Document {
    private topics: Topic[] = [];

    public addTopic(topic: Topic): void {
        this.topics.push(topic);
    }

    public getTopics(): Topic[] {
        return this.topics;
    }

    public getLastTopic(): Topic {
        return this.topics[this.topics.length - 1];
    }

    public getSectionsById(ids: number[]): Section[] {
        const sections: Section[] = [];

        for (const topic of this.topics) {
            const section = topic.getSections().find((section: Section) => ids.includes(section.getId()));

            if (section) {
                const index = ids.findIndex((id: number) => id === section.getId());
                sections[index] = section;
            }
        }

        return sections;
    }
}
