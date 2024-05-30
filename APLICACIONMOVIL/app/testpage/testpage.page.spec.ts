import { ComponentFixture, TestBed } from '@angular/core/testing';
import { TestpagePage } from './testpage.page';

describe('TestpagePage', () => {
  let component: TestpagePage;
  let fixture: ComponentFixture<TestpagePage>;

  beforeEach(() => {
    fixture = TestBed.createComponent(TestpagePage);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
