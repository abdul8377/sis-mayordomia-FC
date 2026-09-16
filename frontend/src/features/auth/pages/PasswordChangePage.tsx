import { useMemo, useState, type FormEvent } from 'react';
import { ArrowRight, Check, KeyRound, Leaf, LockKeyhole, ShieldCheck } from 'lucide-react';
import { useNavigate } from 'react-router-dom';
import { useAuth } from '../../../app/providers/AuthProvider';
import { homePathFor } from '../../../app/router/access';
import { Button, Field, Notice } from '../../../shared/ui';

export function PasswordChangePage() {
  const { user, changePassword } = useAuth();
  const navigate = useNavigate();
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  const requirements = useMemo(() => [
    { label: '12 caracteres como mínimo', valid: password.length >= 12 },
    { label: 'Incluye una letra', valid: /[A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(password) },
    { label: 'Incluye un número o símbolo', valid: /[^A-Za-zÁÉÍÓÚáéíóúÑñ]/.test(password) },
  ], [password]);

  async function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    if (!user) return;
    const values = new FormData(event.currentTarget);
    const confirmation = String(values.get('password_confirmation') ?? '');
    if (password !== confirmation) {
      setError('La confirmación no coincide con la nueva contraseña.');
      return;
    }

    setBusy(true);
    setError('');
    try {
      await changePassword({
        currentPassword: String(values.get('current_password') ?? ''),
        password,
        passwordConfirmation: confirmation,
      });
      navigate(homePathFor({ ...user, must_change_password: false }), { replace: true });
    } catch (caught) {
      setError(caught instanceof Error ? caught.message : 'No pudimos actualizar la contraseña.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <div className="security-page">
      <section className="security-brand-panel">
        <div className="brand brand-light"><span className="brand-symbol"><Leaf size={25} /></span><span>conecta.</span></div>
        <div className="security-story">
          <span className="overline"><ShieldCheck size={15} /> PRIMER INGRESO SEGURO</span>
          <h1>Tu comunidad empieza con una cuenta protegida.</h1>
          <p>Antes de continuar, reemplaza la contraseña temporal que recibiste por una que solo tú conozcas.</p>
          <div className="security-promise"><LockKeyhole size={22} /><span><strong>Tu sesión permanece protegida</strong><small>La nueva contraseña se guarda únicamente en el servidor y nunca se muestra en pantalla.</small></span></div>
        </div>
        <p className="login-bottom">SEGURIDAD · PRIVACIDAD · COMUNIDAD</p>
      </section>

      <section className="security-form-panel">
        <div className="security-form-wrap">
          <span className="security-icon"><KeyRound size={24} /></span>
          <p className="eyebrow">PROTEGE TU ACCESO</p>
          <h2>Crea una nueva contraseña</h2>
          <p className="muted">Una vez actualizada, entrarás directamente a tu espacio en Conecta.</p>

          <form onSubmit={submit}>
            {error && <Notice error>{error}</Notice>}
            <Field label="Contraseña temporal">
              <input name="current_password" type="password" autoComplete="current-password" required autoFocus placeholder="La contraseña que recibiste" />
            </Field>
            <Field label="Nueva contraseña" hint="Usa una frase larga que puedas recordar.">
              <input name="password" type="password" autoComplete="new-password" required minLength={12} value={password} onChange={event => setPassword(event.target.value)} placeholder="Mínimo 12 caracteres" />
            </Field>
            <div className="password-requirements" aria-live="polite">
              {requirements.map(item => <span className={item.valid ? 'is-valid' : ''} key={item.label}><Check size={14} />{item.label}</span>)}
            </div>
            <Field label="Confirma la nueva contraseña">
              <input name="password_confirmation" type="password" autoComplete="new-password" required minLength={12} placeholder="Repítela una vez más" />
            </Field>
            <Button className="full-width" busy={busy} type="submit">Guardar y continuar <ArrowRight size={18} /></Button>
          </form>
        </div>
      </section>
    </div>
  );
}
