import { Component, signal } from '@angular/core';
import { LibroCrudComponent } from './components/libro-crud/libro-crud';

@Component({
  imports: [LibroCrudComponent],
  selector: 'app-root',
  standalone: true,
  styleUrl: './app.css',
  templateUrl: './app.html',
})
export class App {
  protected readonly title = signal('Gestión de libros');
}
