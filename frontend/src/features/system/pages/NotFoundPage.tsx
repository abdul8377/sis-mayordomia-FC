import { ArrowLeft, Compass, Leaf } from 'lucide-react';
import { Link } from 'react-router-dom';

export function NotFoundPage() {
  return (
    <div className="not-found-page page-enter">
      <div className="not-found-art"><span><Leaf size={34} /></span><Compass size={86} strokeWidth={0.8} /></div>
      <p className="eyebrow">RUTA NO ENCONTRADA</p>
      <h1>Esta parte del camino no existe.</h1>
      <p>Puede que el enlace haya cambiado o que hayas llegado aquí desde una dirección antigua.</p>
      <Link to="/" className="button button-primary"><ArrowLeft size={17} />Volver a mi comunidad</Link>
    </div>
  );
}
