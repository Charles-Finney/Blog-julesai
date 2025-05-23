import React from 'react';
import PostList from './PostList';
import { useTranslation } from 'react-i18next';

function ProjectsPage() {
  const { t } = useTranslation();
  return (
    <div>
      <h1 style={{ textAlign: 'center', marginBottom: '20px' }}>{t('projectsTitle')}</h1>
      <PostList postType="project" />
    </div>
  );
}

export default ProjectsPage;
