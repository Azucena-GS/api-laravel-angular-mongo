export interface Libro {
  id: string;
  title: string;
  author: string;
  published_year: number;
  genre: string;
  description: string;
}

export type LibroInput = Omit<Libro, 'id'>;