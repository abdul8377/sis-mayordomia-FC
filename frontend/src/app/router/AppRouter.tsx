import { Navigate, Route, Routes } from 'react-router-dom';
import { AppLayout } from '../layouts/AppLayout';
import { PublicOnly, RequirePasswordUpdated, RequireRoles, RequireSession } from './RouteGuards';
import { GP_MANAGEMENT_ROLES, STAFF_ROLES } from './access';
import { LoginPage } from '../../features/auth/pages/LoginPage';
import { PasswordChangePage } from '../../features/auth/pages/PasswordChangePage';
import { DashboardPage } from '../../features/dashboard/pages/DashboardPage';
import { PeoplePage } from '../../features/people/pages/PeoplePage';
import { GroupsPage } from '../../features/small-groups/pages/GroupsPage';
import { MeetingsPage } from '../../features/meetings/pages/MeetingsPage';
import { ActivitiesPage, ActivityDetailPage } from '../../features/youth-activities/pages/ActivitiesPage';
import { OpportunitiesPage } from '../../features/gp-ja-bridge/pages/OpportunitiesPage';
import { FollowUpsPage } from '../../features/follow-ups/pages/FollowUpsPage';
import { SurveysPage } from '../../features/surveys/pages/SurveysPage';
import { ServicePage } from '../../features/service-needs/pages/ServicePage';
import { ProfilePage } from '../../features/profile/pages/ProfilePage';
import { SettingsPage } from '../../features/settings/pages/SettingsPage';
import { IndicatorsPage } from '../../features/reporting/pages/IndicatorsPage';
import { HelpPage } from '../../features/help/pages/HelpPage';
import { NotFoundPage } from '../../features/system/pages/NotFoundPage';

export function AppRouter() {
  return (
    <Routes>
      <Route element={<PublicOnly />}>
        <Route path="/ingresar" element={<LoginPage />} />
      </Route>

      <Route element={<RequireSession />}>
        <Route path="/cambiar-contrasena" element={<PasswordChangePage />} />

        <Route element={<RequirePasswordUpdated />}>
          <Route element={<AppLayout />}>
            <Route index element={<DashboardPage />} />

            <Route element={<RequireRoles roles={STAFF_ROLES} />}>
              <Route path="/grupos" element={<GroupsPage />} />
              <Route path="/personas" element={<PeoplePage />} />
              <Route path="/servicio" element={<ServicePage />} />
              <Route path="/indicadores" element={<IndicatorsPage />} />
            </Route>

            <Route element={<RequireRoles roles={GP_MANAGEMENT_ROLES} />}>
              <Route path="/reuniones" element={<MeetingsPage />} />
              <Route path="/seguimiento" element={<FollowUpsPage />} />
            </Route>

            <Route path="/actividades" element={<ActivitiesPage />} />
            <Route path="/actividades/:id" element={<ActivityDetailPage />} />
            <Route path="/oportunidades" element={<OpportunitiesPage />} />
            <Route path="/encuestas" element={<SurveysPage />} />
            <Route path="/perfil" element={<ProfilePage />} />
            <Route path="/ayuda" element={<HelpPage />} />

            <Route element={<RequireRoles roles={['admin']} />}>
              <Route path="/configuracion" element={<SettingsPage />} />
            </Route>

            <Route path="/404" element={<NotFoundPage />} />
            <Route path="*" element={<Navigate to="/404" replace />} />
          </Route>
        </Route>
      </Route>
    </Routes>
  );
}
