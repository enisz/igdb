import { Injectable } from '@angular/core';
import { BehaviorSubject, Observable } from 'rxjs';
import { IToast } from '../interface/toast.interface';

@Injectable({
  providedIn: 'root'
})
export class ToastService {
  private idCounter = 0;
  private toastSubject: BehaviorSubject<{[key: number]: IToast}>;

  constructor() {
    this.toastSubject = new BehaviorSubject<{[key: number]: IToast}>({});
  }

  public getToastObservable(): Observable<{[key: number]: IToast}> {
    return this.toastSubject.asObservable();
  }

  public addToast(toast: IToast): number {
    const toasts = this.toastSubject.value;
    const id = this.idCounter++;

    toasts[id] = toast;
    this.toastSubject.next(toasts);
    return id;
  }

  public removeToast(id: number): IToast {
    const toasts = this.toastSubject.value;
    const toast = toasts[id];
    delete toasts[id];
    this.toastSubject.next(toasts);

    return toast;
  }

  public getActiveIds(): number[] {
    return Object.keys(this.toastSubject.value).map((id: string) => parseInt(id));
  }

  public closeAll(): void {
    this.toastSubject.next({});
  }

  public info(message: string, timeout: number = 5000): number {
    return this.addToast({ message, timeout, type: 'info' });
  }

  public warning(message: string, timeout: number = 5000): number {
    return this.addToast({ message, timeout, type: 'warning' });
  }

  public error(message: string, timeout: number = 5000): number {
    return this.addToast({ message, timeout, type: 'danger' });
  }

  public success(message: string, timeout: number = 5000): number {
    return this.addToast({ message, timeout, type: 'success' });
  }

  public light(message: string, timeout: number = 5000): number {
    return this.addToast({ message, timeout, type: 'light' });
  }

  public dark(message: string, timeout: number = 5000): number {
    return this.addToast({ message, timeout, type: 'dark' });
  }
}
