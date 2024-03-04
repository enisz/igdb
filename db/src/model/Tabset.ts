import { Tab } from './Tab';

export class Tabset {
    private id: string;
    private name: string;
    private tabs: Tab[] = [];

    public constructor(id: string, name: string) {
        this.id = id;
        this.name = name;
    }

    public getId(): string {
        return this.id;
    }

    public getName(): string {
        return this.name;
    }

    public addTab(id: string, title: string): Tab {
        const tab = new Tab(id, title, this.tabs.length === 0);
        this.tabs.push(tab);
        return tab;
    }

    public getTab(tabId: string): Tab | null {
        return this.tabs.find((tab: Tab) => tab.getId() === tabId) || null;
    }

    public getTabs(): Tab[] {
        return this.tabs;
    }
}
