import { Location } from '@angular/common';
import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ScrollSpyService {
  private activeSectionSubject: BehaviorSubject<string[]> = new BehaviorSubject<string[]>([]);
  private intersectionObserver: IntersectionObserver;
  private visibleElements: string[] = [];

  constructor(
    private readonly locationService: Location,
  ) {
    const intersectionOptions: IntersectionObserverInit = {
      rootMargin: '-11% 0px -89% 0px',
    };

    this.intersectionObserver = new IntersectionObserver(this.intersectionCallback.bind(this), intersectionOptions);
  }

  public observe(element: Element): void {
    this.intersectionObserver.observe(element);
  }

  public unobserve(element: Element): void {
    this.intersectionObserver.observe(element);
  }

  public disconnect(): void {
    this.intersectionObserver.disconnect();
  }

  public getActiveObservable(): Observable<string[]> {
    return this.activeSectionSubject.asObservable();
  }

  private intersectionCallback(entries: IntersectionObserverEntry[], observer: IntersectionObserver): void {
    for (const entry of entries) {
      const { target, isIntersecting } = entry as IntersectionObserverEntry;
      const index = this.visibleElements.indexOf(target.id);

      if (isIntersecting && index < 0) {
        this.visibleElements.push(target.id);
        this.locationService.replaceState(`${this.locationService.path(false)}#${target.id}`);
      } else if (!isIntersecting && index > -1) {
        this.visibleElements.splice(index, 1);
      }
    }

    this.activeSectionSubject.next(this.visibleElements);
  }
}
