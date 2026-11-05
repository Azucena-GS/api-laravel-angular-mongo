import { CommonModule } from '@angular/common';
import { HttpErrorResponse } from '@angular/common/http';
import { Component, OnInit, inject, signal } from '@angular/core';
import { FormBuilder, ReactiveFormsModule, Validators } from '@angular/forms';
import { TimeoutError } from 'rxjs';
import { Libro, LibroInput } from '../../models/libro.model';
import { LibroService } from '../../services/libro.service';

@Component({
  selector: 'app-libro-crud',
  standalone: true,
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './libro-crud.html',
  styleUrl: './libro-crud.css',
})
export class LibroCrudComponent implements OnInit {
  private readonly formBuilder = inject(FormBuilder).nonNullable;
  private readonly libroService = inject(LibroService);

  readonly libros = signal<Libro[]>([]);
  readonly editingId = signal<string | null>(null);
  readonly loading = signal(false);
  readonly saving = signal(false);
  readonly deletingId = signal<string | null>(null);
  readonly errorMessage = signal('');

  readonly libroForm = this.formBuilder.group({
    title: ['', [Validators.required, Validators.maxLength(255)]],
    author: ['', [Validators.required, Validators.maxLength(255)]],
    published_year: [new Date().getFullYear(), [Validators.required, Validators.min(0)]],
    genre: ['', [Validators.required, Validators.maxLength(100)]],
    description: ['Sin descripción disponible.', Validators.required],
  });

  ngOnInit(): void {
    this.loadLibros();
  }

  loadLibros(): void {
    this.loading.set(true);
    this.errorMessage.set('');
    this.libroService.getLibros().subscribe({
      next: (libros) => {
        this.libros.set(libros);
        this.loading.set(false);
      },
      error: (error: unknown) => {
        this.errorMessage.set(
          error instanceof TimeoutError
            ? 'La solicitud tardó demasiado. Comprueba la conexión e inténtalo de nuevo.'
            : this.getErrorMessage(error, 'No se pudieron cargar los libros.'),
        );
        this.loading.set(false);
      },
    });
  }

  onSubmit(): void {
    if (this.libroForm.invalid || this.saving()) {
      this.libroForm.markAllAsTouched();
      return;
    }

    const libro = this.libroForm.getRawValue() satisfies LibroInput;
    const id = this.editingId();
    const request = id
      ? this.libroService.updateLibro(id, libro)
      : this.libroService.createLibro(libro);

    this.saving.set(true);
    this.errorMessage.set('');
    request.subscribe({
      next: (savedLibro) => {
        this.libros.update((libros) =>
          id
            ? libros.map((current) => (current.id === savedLibro.id ? savedLibro : current))
            : [...libros, savedLibro],
        );
        this.resetForm();
        this.saving.set(false);
      },
      error: (error: unknown) => {
        this.errorMessage.set(this.getErrorMessage(error, 'No se pudo guardar el libro.'));
        this.saving.set(false);
      },
    });
  }

  editLibro(libro: Libro): void {
    this.editingId.set(libro.id);
    this.libroForm.setValue({
      title: libro.title,
      author: libro.author,
      published_year: libro.published_year,
      genre: libro.genre,
      description: libro.description,
    });
    document.getElementById('form-title')?.scrollIntoView({ behavior: 'smooth' });
  }

  deleteLibro(libro: Libro): void {
    if (!window.confirm(`¿Eliminar «${libro.title}»?`)) {
      return;
    }

    this.errorMessage.set('');
    this.deletingId.set(libro.id);
    this.libroService.deleteLibro(libro.id).subscribe({
      next: () => {
        this.libros.update((libros) => libros.filter((current) => current.id !== libro.id));
        if (this.editingId() === libro.id) {
          this.resetForm();
        }
        this.deletingId.set(null);
      },
      error: (error: unknown) => {
        this.errorMessage.set(this.getErrorMessage(error, 'No se pudo eliminar el libro.'));
        this.deletingId.set(null);
      },
    });
  }

  resetForm(): void {
    this.libroForm.reset({
      title: '',
      author: '',
      published_year: new Date().getFullYear(),
      genre: '',
      description: 'Sin descripción disponible.',
    });
    this.editingId.set(null);
  }

  private getErrorMessage(error: unknown, fallback: string): string {
    if (error instanceof HttpErrorResponse) {
      const message = error.error?.message;
      if (typeof message === 'string') {
        return message;
      }

      if (error.status === 0) {
        return 'No se pudo conectar con el servidor. Comprueba que la API esté activa.';
      }
    }

    return fallback;
  }
}
