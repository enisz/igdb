import { Component, HostListener, Input, OnDestroy, OnInit } from '@angular/core';
import { ToastService } from '../../../service/toast.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-toast',
  standalone: true,
  imports: [CommonModule],
  templateUrl: './toast.component.html',
  styleUrl: './toast.component.scss'
})
export class ToastComponent implements OnInit, OnDestroy {
  @Input('id') public id: number = -1;
  @Input('message') public message: string = '';
  @Input('type') public type: string = '';
  @Input('timeout') public timeout: number = 5000;
  public now: number = 0;
  public remaining: number = 0;
  private timeoutHanlder: ReturnType<typeof setTimeout> | null = null;

  public constructor(
    private readonly toastService: ToastService,
  ) { }

  public ngOnInit(): void {
    this.start();
  }

  public ngOnDestroy(): void {
    if(this.timeoutHanlder !== null) {
      clearInterval(this.timeoutHanlder);
    }
  }

  public getIcon(): string {
    switch(this.type) {
      case 'success':
        return 'fa-circle-check';
        break;

      case 'warning':
        return 'fa-circle-exclamation';
        break;

      case 'danger':
        return 'fa-circle-xmark';
        break;
      default:
        return 'fa-info-circle';
        break;
    }
  }

  public start(): void {
    this.now = Date.now();
    this.timeoutHanlder = setTimeout(() => this.close(), this.timeout);
  }

  public pause(): void {

    if (this.timeoutHanlder !== null) {
      clearTimeout(this.timeoutHanlder);
      this.timeoutHanlder = null;
    }

    this.remaining = this.timeout - (Date.now() - this.now);
  }

  public resume(): void {
    if (this.timeoutHanlder !== null) {
      clearTimeout(this.timeoutHanlder);
      this.timeoutHanlder = null;
    }

    this.timeoutHanlder = setTimeout(() => this.close(), this.remaining);
  }

  public close(): void {
    this.toastService.removeToast(this.id);
  }

  @HostListener('mouseenter')
  private mouseEnter(): void {
    this.pause();
  }

  @HostListener('mouseleave')
  private mouseLeave(): void {
    this.resume();
  }
}
