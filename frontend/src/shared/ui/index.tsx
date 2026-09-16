import { useEffect, useId, useRef, type ReactNode, type ButtonHTMLAttributes } from 'react';
import { AlertCircle, ArrowRight, Check, LoaderCircle, X } from 'lucide-react';
import { initials, colors } from '../utils/format';

export function Button({ children, variant = 'primary', busy, ...props }: ButtonHTMLAttributes<HTMLButtonElement> & { variant?: 'primary' | 'secondary' | 'ghost' | 'danger'; busy?: boolean }) {
  return <button {...props} disabled={props.disabled || busy} className={`button button-${variant} ${props.className || ''}`}>{busy ? <LoaderCircle size={17} className="spin" /> : null}{children}</button>;
}
export function Avatar({ name, size = 'md', index = 0 }: { name: string; size?: 'sm' | 'md' | 'lg'; index?: number }) { return <span className={`avatar avatar-${size} ${colors[index % colors.length]}`} aria-label={name}>{initials(name)}</span>; }
export function Badge({ children, tone = 'green' }: { children: ReactNode; tone?: 'green' | 'amber' | 'muted' | 'purple' | 'red' }) { return <span className={`badge badge-${tone}`}>{children}</span>; }
export function PageHeading({ eyebrow, title, description, action }: { eyebrow: string; title: string; description: string; action?: ReactNode }) { return <div className="page-heading"><div><p className="eyebrow">{eyebrow}</p><h1>{title}</h1><p className="page-description">{description}</p></div>{action}</div>; }
export function Loading() { return <div className="loading-state" role="status"><LoaderCircle className="spin" size={28} /><span>Preparando tu espacio…</span></div>; }
export function ErrorState({ error, retry }: { error: unknown; retry?: () => void }) { return <div className="error-state" role="alert"><AlertCircle size={24} /><div><strong>No pudimos cargar la información</strong><p>{error instanceof Error ? error.message : 'Comprueba tu conexión e inténtalo nuevamente.'}</p></div>{retry && <Button variant="secondary" onClick={retry}>Reintentar</Button>}</div>; }
export function EmptyState({ title, text, action }: { title: string; text: string; action?: ReactNode }) { return <div className="empty-state"><div className="empty-symbol"><ArrowRight size={25} /></div><h3>{title}</h3><p>{text}</p>{action}</div>; }
export function Notice({ children, error = false }: { children: ReactNode; error?: boolean }) { return <div role={error ? 'alert' : 'status'} className={`notice ${error ? 'notice-error' : ''}`}>{error ? <AlertCircle size={18} /> : <Check size={18} />}{children}</div>; }
export function Modal({ title, description, children, onClose, wide = false }: { title: string; description?: string; children: ReactNode; onClose: () => void; wide?: boolean }) {
  const ref = useRef<HTMLDialogElement>(null); const id = useId();
  useEffect(() => { const dialog = ref.current; dialog?.showModal(); return () => dialog?.close(); }, []);
  return <dialog ref={ref} className={`modal ${wide ? 'modal-wide' : ''}`} aria-labelledby={id} onCancel={onClose} onClick={event => { if (event.target === ref.current) onClose(); }}><div className="modal-heading"><div><h2 id={id}>{title}</h2>{description && <p>{description}</p>}</div><button className="icon-button" aria-label="Cerrar" onClick={onClose}><X size={20} /></button></div>{children}</dialog>;
}
export function Field({ label, children, hint }: { label: string; children: ReactNode; hint?: string }) { return <label className="field"><span>{label}</span>{children}{hint && <small>{hint}</small>}</label>; }
