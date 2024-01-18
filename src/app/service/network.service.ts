import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable, fromEvent, merge } from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class NetworkService {
  private networkStatusSubject: BehaviorSubject<boolean>;

  constructor() {
    this.networkStatusSubject = new BehaviorSubject(window.navigator.onLine);

    merge(
      fromEvent(window, 'online'),
      fromEvent(window, 'offline')
    ).subscribe((event: Event) => this.networkStatusSubject.next(event.type === 'online'))
  }

  public getStatusObservable(): Observable<boolean> {
    return this.networkStatusSubject.asObservable();
  }

  public updateStatus(status: boolean): void {
    this.networkStatusSubject.next(status);
  }
}
