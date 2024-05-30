import { ComponentFixture, TestBed } from '@angular/core/testing';
import { LlPage } from './ll.page';

describe('LlPage', () => {
  let component: LlPage;
  let fixture: ComponentFixture<LlPage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(LlPage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
