import { HttpClient } from '@angular/common/http';
import { Injectable, inject } from '@angular/core';
import { map, Observable, timeout } from 'rxjs';
import { Libro, LibroInput } from '../models/libro.model';

interface ApiCollection<T> {
  data: T[];
}

interface ApiResource<T> {
  data: T;
}

@Injectable({ providedIn: 'root' })
export class LibroService {
  private readonly apiUrl = '/api/libros';
  private readonly http = inject(HttpClient);

  getLibros(): Observable<Libro[]> {
    return this.http.get<ApiCollection<Libro>>(this.apiUrl).pipe(
      timeout({ first: 10000 }),
      map((response) => response.data),
    );
  }

  createLibro(libro: LibroInput): Observable<Libro> {
    return this.http.post<ApiResource<Libro>>(this.apiUrl, libro).pipe(
      map((response) => response.data),
    );
  }

  updateLibro(id: string, libro: LibroInput): Observable<Libro> {
    return this.http.put<ApiResource<Libro>>(`${this.apiUrl}/${id}`, libro).pipe(
      map((response) => response.data),
    );
  }

  deleteLibro(id: string): Observable<void> {
    return this.http.delete<void>(`${this.apiUrl}/${id}`);
  }
}
