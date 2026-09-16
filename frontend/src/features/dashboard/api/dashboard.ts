import { api } from '../../../shared/api/client';
import type { Dashboard } from '../../../shared/types/domain';
export const getDashboard = () => api.get<Dashboard>('/dashboard');
