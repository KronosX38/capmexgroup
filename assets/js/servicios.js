/* ========================================================================= */
/*	Modal Services
/* ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
  const servicios = {
    'contable': {
      titulo: 'Servicio Contable',
      texto: [
        'Asesoría contable.',
        'Cumplimiento de obligaciones fiscales en materia federal y estatal.',
        'Control de inventarios.',
        'Auditoría contable financiera.',
        'Seguridad Social.',
        'Asesoría en la aplicación de normas de información financiera.',
        'Trámites ante el SAT, IMSS e INFONAVIT.',
        'Reingeniería fiscal.',
        'Implementación de asesoría fiscal especializada con estrategias financieras y fiscales.'
      ],
      imagen: './assets/img/fondo-negro.jpg'
    },
    'juridico': {
      titulo: 'Servicio Jurídico',
      texto: [
        'Asesoría en procedimientos administrativos.',
        'Asesoría en procedimientos y/o facultades de comprobación de autoridades fiscales y estatales.',
        'Realización de trámites ante el SAT, IMSS e INFONAVIT.',
        'Amparo contra leyes.',
        'Interposición de medio de defensa en materia fiscal, recursos de revocación, recursos de inconformidad y consultas especializadas en materia fiscal.',
        'Tramitación y gestión de quejas y acuerdos conclusivos ante la Procuraduría de la Defensa del Contribuyente.'
      ],
      imagen: './assets/img/fondo-negro.jpg'
    },
    'capital-humano': {
      titulo: 'Capital Humano',
      texto: [
        'Servicios de maquila de nómina.',
        'Asesoría para la inscripción en el padrón REPSE de la STPS.',
        'Administración de nómina a través de empresas inscritas al padrón de REPSE.',
        'Administración de nómina.',
        'Remuneración a socios, accionistas y personal.'
      ],
      imagen: './assets/img/fondo-negro.jpg'
    },
    'corporativo': {
      titulo: 'Corporativo',
      texto: [
        'Constitución de personas morales.',
        'Fusión y rescisión de sociedades mercantiles.',
        'Liquidación de personas morales.',
        'Auditoriía jurídica de libros corporativos.',
        'Reingeniería Corporativa.',
        'Revisión de contratos mercantiles y civiles.',
        'Servicios Fiduciarios.',
        'Adquisiciones de empresas.',
        'Due Diligence y Compliance.',
        'Asesoría para la implementación de políticas y procedimientos.',
        'Consultoría legal-financiera para intermediarios financieros.',
        'Reestructuración de deuda.',
        'Cumplimiento regulatorio-Backoffice PLD/FT.',
        'Gobierno corporativo.',
        'Back Office legal para financieras.',
        'Back Office legal para inmobiliarias.'
      ],
      imagen: './assets/img/fondo-negro.jpg'
    },
    'inversiones': {
      titulo: 'Inversiones',
      texto: [
        'Vehículos jurídicos para proyección de inversión.',
        'Creación de portafolios de inversión.',
        'Desarrollo de proyectos inmobiliarios.'
      ],
      imagen: './assets/img/fondo-negro.jpg'
    },
    'financieros': {
      titulo: 'Servicios Financieros',
      texto: [
        'Créditos para capital de trabajo.',
        'Créditos de nómina.',
        'Arrendamiento financiero.',
        'Factoraje financiero.',
        'Fideicomisos.'
      ],
      imagen: './assets/img/fondo-negro.jpg'
    }
  };

  const modalTitulo = document.getElementById('modalServicioLabel');
  const modalTexto = document.getElementById('modalServicioTexto');
  const modalImagen = document.getElementById('modalServicioImagen');

  document.querySelectorAll('.btn-servicio').forEach(boton => {
    boton.addEventListener('click', () => {
      const tipo = boton.getAttribute('data-servicio');
      const servicio = servicios[tipo];

      if (servicio) {
        modalTitulo.textContent = servicio.titulo;

        // Construimos el contenido con íconos
        modalTexto.innerHTML = servicio.texto.map(texto => `
          <p><i class="bi bi-check2-circle me-2 text-primary"></i> ${texto}</p>
        `).join('');

        modalImagen.src = servicio.imagen;
        modalImagen.alt = servicio.titulo;
      }
    });
  });
});

/* ========================================================================= */
/*	Close Modal
/* ========================================================================= */
  $(".cerrarModal").click(function(){
  $("#modalContractCandidatesPre").modal('hide')
});