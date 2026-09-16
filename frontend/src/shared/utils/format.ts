export const shortDate = (value: string) => new Intl.DateTimeFormat('es-PE', { day: 'numeric', month: 'short', timeZone: 'America/Lima' }).format(new Date(value));
export const fullDate = (value: string) => new Intl.DateTimeFormat('es-PE', { weekday: 'long', day: 'numeric', month: 'long', timeZone: 'America/Lima' }).format(new Date(value));
export const time = (value: string) => new Intl.DateTimeFormat('es-PE', { hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'America/Lima' }).format(new Date(value));
export const initials = (name: string) => name.trim().split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase();
export const colors = ['sage', 'sand', 'lavender', 'rose', 'sky'];
export const percent = (value: number | null) => value === null ? '—' : `${Math.round(value)}%`;
export const localDateTime = (date: Date) => new Date(date.getTime() - date.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
