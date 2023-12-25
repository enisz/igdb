import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class SearchService {
  private recentSearches: string[] = [];
  private searchModalVisibleSubject: BehaviorSubject<boolean>;

  constructor() {
    this.searchModalVisibleSubject = new BehaviorSubject(false);
    const history = window.localStorage.getItem('search-history') || null;
    this.recentSearches = typeof history === 'string' ? JSON.parse(history) : [];
  }

  public setModalVisibility(visible: boolean): void {
    this.searchModalVisibleSubject.next(visible);
  }

  public searchModalVisibleObservable(): Observable<boolean> {
    return this.searchModalVisibleSubject.asObservable();
  }

  public addRecentSearchTerm(term: string): void {
    if (!this.recentSearches.includes(term)) {
      this.recentSearches.push(term);
      this.synchronize();
    }
  }

  public removeRecentSearchTerm(term: string): void {
    this.synchronize();
  }

  private synchronize(): void {
    window.localStorage.setItem('search-history', JSON.stringify(this.recentSearches));
  }
}
