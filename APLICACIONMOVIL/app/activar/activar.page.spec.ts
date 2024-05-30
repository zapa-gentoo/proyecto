import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ActivarPage } from './activar.page';

describe('ActivarPage', () => {
  let component: ActivarPage;
  let fixture: ComponentFixture<ActivarPage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(ActivarPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
