import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { api } from '../api/client';

export function useData<T>(path: string) { return useQuery({ queryKey: [path], queryFn: () => api.get<T>(path) }); }
export function useSave<T = unknown>(path: string, method: 'post' | 'put' | 'patch' = 'post') {
  const client = useQueryClient();
  return useMutation({ mutationFn: (data: T) => api[method](path, data), onSuccess: () => client.invalidateQueries() });
}
