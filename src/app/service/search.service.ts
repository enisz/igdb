import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, Subscription } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private searchHistoryKey = 'search-history';
  private searchModalVisibleSubject: BehaviorSubject<boolean>;
  private searchHistorySubject: BehaviorSubject<string[]>;
  private historyChangeSubscription: Subscription;

  constructor() {
    this.searchHistorySubject = new BehaviorSubject<string[]>([]);
    this.searchModalVisibleSubject = new BehaviorSubject(false);
    const history = localStorage.getItem(this.searchHistoryKey);

    if (history) {
      try {
        this.searchHistorySubject.next(JSON.parse(history));
      } catch {
        console.error('Search history is corrupted!');
        this.clearHistory();
      }
    }

    this.historyChangeSubscription = this.searchHistorySubject.subscribe(
      (history: string[]) => {
        localStorage.setItem(this.searchHistoryKey, JSON.stringify(history));
      },
    );
  }

  public setModalVisibility(visible: boolean): void {
    this.searchModalVisibleSubject.next(visible);
  }

  public searchModalVisibleObservable(): Observable<boolean> {
    return this.searchModalVisibleSubject.asObservable();
  }

  public getHistory(): string[] {
    return this.searchHistorySubject.value;
  }

  public searchHistoryObservable(): Observable<string[]> {
    return this.searchHistorySubject.asObservable();
  }

  public addHistoryItem(term: string): void {
    const history = this.searchHistorySubject.value;

    if (!history.includes(term)) {
      history.push(term);
    } else {
      history.push(
        history.splice(
          history.indexOf(term), 1
        )[0]
      )
    }

    this.searchHistorySubject.next(history);
  }

  public removeHistoryItem(term: string): void {
    const history = this.searchHistorySubject.value;
    history.splice(history.indexOf(term), 1)

    this.searchHistorySubject.next(history);
  }

  public clearHistory(): void {
    this.searchHistorySubject.next([]);
  }
}
