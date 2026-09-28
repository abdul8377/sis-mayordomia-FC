import { useEffect, useState, type FormEvent } from 'react';
import { BellRing, BookOpenText, Check, Clock3, Heart, Layers3, Settings2, Sparkles, UsersRound } from 'lucide-react';
import { useData, useSave } from '../../../shared/hooks/useData';
import { Button, ErrorState, Field, Loading, Notice, PageHeading } from '../../../shared/ui';
import type { CatalogItem, Catalogs } from '../../../shared/types/domain';

interface SettingsResponse { data: { church_name: string; absence_threshold: number } }

const catalogCards: { key: keyof Catalogs; label: string; description: string; icon: typeof Sparkles }[] = [
  { key: 'talents', label: 'Talentos', description: 'Habilidades que una persona puede aportar.', icon: Sparkles },
  { key: 'interests', label: 'Intereses', description: 'Temas que ayudan a conectar personas y actividades.', icon: Heart },
  { key: 'activity_types', label: 'Tipos de actividad', description: 'Clasifica los encuentros del Ministerio Joven.', icon: Layers3 },
  { key: 'responsibilities', label: 'Responsabilidades', description: 'Tareas concretas que pueden asumirse en una actividad.', icon: UsersRound },
  { key: 'availability_slots', label: 'Disponibilidad', description: 'Fracciones de tiempo usadas en el perfil de participación.', icon: Clock3 },
];

function activeCount(items: CatalogItem[]) {
  return items.filter(item => item.is_active !== false).length;
}

export function SettingsPage() {
  const settings = useData<SettingsResponse>('/settings');
  const catalogs = useData<Catalogs>('/catalogs?include_inactive=1');
  const save = useSave<{ church_name: string; absence_threshold: number }>('/settings', 'put');
  const [churchName, setChurchName] = useState('');
  const [threshold, setThreshold] = useState(3);

  useEffect(() => {
    if (!settings.data) return;
    setChurchName(settings.data.data.church_name);
    setThreshold(settings.data.data.absence_threshold);
  }, [settings.data]);

  function submit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();
    save.mutate({ church_name: churchName.trim(), absence_threshold: threshold });
  }

  if (settings.isPending || catalogs.isPending) return <Loading />;
  if (settings.error) return <ErrorState error={settings.error} retry={() => void settings.refetch()} />;
  if (catalogs.error) return <ErrorState error={catalogs.error} retry={() => void catalogs.refetch()} />;

  return (
    <div className="page-enter settings-page">
      <PageHeading eyebrow="ADMINISTRACIÓN" title="Configuración de la comunidad" description="Ajustes generales y catálogos que mantienen consistente la experiencia en todos los módulos." />

      <div className="settings-grid">
        <form className="card settings-form" onSubmit={submit}>
          <div className="section-heading"><div><p className="eyebrow">AJUSTES GENERALES</p><h2>Identidad y acompañamiento</h2></div><Settings2 size={20} /></div>
          {save.error && <Notice error>{save.error.message}</Notice>}
          {save.isSuccess && <Notice>La configuración se actualizó correctamente.</Notice>}
          <Field label="Nombre de la iglesia" hint="Se utiliza como referencia de la comunidad dentro del sistema.">
            <input value={churchName} onChange={event => setChurchName(event.target.value)} maxLength={120} required />
          </Field>
          <Field label="Ausencias consecutivas para generar alerta" hint="El seguimiento se activa al cerrar reuniones válidas.">
            <div className="number-setting">
              <button type="button" aria-label="Reducir umbral" onClick={() => setThreshold(value => Math.max(1, value - 1))}>−</button>
              <input type="number" min={1} max={12} value={threshold} onChange={event => setThreshold(Number(event.target.value))} required />
              <button type="button" aria-label="Aumentar umbral" onClick={() => setThreshold(value => Math.min(12, value + 1))}>+</button>
            </div>
          </Field>
          <div className="settings-callout"><BellRing size={18} /><span><strong>Seguimiento humano, no automático.</strong><small>El umbral crea una alerta para acompañar; nunca envía mensajes por sí solo.</small></span></div>
          <Button type="submit" busy={save.isPending}>Guardar configuración <Check size={17} /></Button>
        </form>

        <aside className="card settings-principles">
          <span className="settings-principles-icon"><BookOpenText size={22} /></span>
          <p className="eyebrow">CRITERIO DE DISEÑO</p>
          <h2>La configuración debe ser simple de entender.</h2>
          <p>Los roles conceden acceso. Las responsabilidades describen cómo alguien sirve. Mantener esa separación evita permisos accidentales y datos ambiguos.</p>
          <div className="principle-points"><span><Check size={15} />Roles fijos y acumulables</span><span><Check size={15} />Catálogos editables sin perder historial</span><span><Check size={15} />Reglas de negocio validadas por el servidor</span></div>
        </aside>
      </div>

      <section className="settings-catalog-section">
        <div className="page-subheading"><div><p className="eyebrow">CATÁLOGOS</p><h2>El lenguaje común del sistema</h2><p>Estos elementos alimentan perfiles, oportunidades, actividades y recomendaciones.</p></div><span className="catalog-total">{Object.values(catalogs.data ?? {}).reduce((sum, items) => sum + items.length, 0)} registros</span></div>
        <div className="catalog-grid">
          {catalogCards.map(card => {
            const items = catalogs.data?.[card.key] ?? [];
            const Icon = card.icon;
            return <article className="catalog-card" key={card.key}><span className="catalog-card-icon"><Icon size={19} /></span><div><h3>{card.label}</h3><p>{card.description}</p></div><div className="catalog-card-meta"><strong>{activeCount(items)}</strong><span>activos de {items.length}</span></div><div className="catalog-preview">{items.slice(0, 4).map(item => <span className={item.is_active === false ? 'is-inactive' : ''} key={item.id}>{item.name}</span>)}{items.length > 4 && <span>+{items.length - 4}</span>}</div></article>;
          })}
        </div>
      </section>
    </div>
  );
}
