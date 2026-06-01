<?php

declare(strict_types=1);

namespace RDT\Corralon\Admin;

use RDT\Corralon\Domain\PresupuestoEstado;
use RDT\Corralon\Repositories\PresupuestoRepositoryInterface;

class AdminPresupuestosPage
{
    private const SLUG    = 'corralon-presupuestos';
    private const POR_PAG = 20;

    public function __construct(
        private readonly PresupuestoRepositoryInterface $repository,
    ) {}

    public function register(): void
    {
        add_action('admin_menu',            [$this, 'addMenuPage']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    public function addMenuPage(): void
    {
        add_submenu_page(
            'woocommerce',
            'Presupuestos',
            'Presupuestos',
            'manage_woocommerce',
            self::SLUG,
            [$this, 'render']
        );
    }

    public function enqueueAssets(): void
    {
        if (empty($_GET['page']) || $_GET['page'] !== self::SLUG) {
            return;
        }

        wp_enqueue_style(
            'rdt-admin-presupuestos',
            RDT_CORRALON_URL . 'assets/css/admin-presupuestos.css',
            [],
            '1.0.0'
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('No tenés permiso para acceder a esta página.', 'corralon-materiales'));
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id > 0) {
            $this->renderDetalle($id);
        } else {
            $this->renderListado();
        }
    }

    private function renderListado(): void
    {
        $pagina       = isset($_GET['pag']) ? max(1, (int) $_GET['pag']) : 1;
        $total        = $this->repository->contarTotal();
        $paginas      = $total > 0 ? (int) ceil($total / self::POR_PAG) : 1;
        $presupuestos = $this->repository->findAll($pagina, self::POR_PAG);
        $updated      = isset($_GET['updated']) ? (int) $_GET['updated'] : 0;
        ?>
        <div class="wrap rdt-presupuestos">
            <h1 class="wp-heading-inline">Presupuestos</h1>
            <hr class="wp-header-end">

            <?php if ($updated === 1) : ?>
                <div class="notice notice-success is-dismissible"><p>Cambios guardados.</p></div>
            <?php endif; ?>

            <table class="wp-list-table widefat fixed striped rdt-presupuestos__tabla">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Fecha</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Productos</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($presupuestos)) : ?>
                        <tr><td colspan="7">No hay presupuestos aún.</td></tr>
                    <?php else : ?>
                        <?php foreach ($presupuestos as $p) :
                            $detUrl      = admin_url('admin.php?page=' . self::SLUG . '&id=' . $p->id);
                            $estadoLabel = $p->estado === PresupuestoEstado::RESPONDIDO ? 'Respondido' : 'Pendiente';
                            $estadoClass = $p->estado === PresupuestoEstado::RESPONDIDO ? 'rdt-badge--respondido' : 'rdt-badge--pendiente';
                        ?>
                        <tr>
                            <td><?php echo esc_html((string) $p->id); ?></td>
                            <td><?php echo esc_html($p->fecha->format('d/m/Y H:i')); ?></td>
                            <td><?php echo esc_html($p->nombre); ?></td>
                            <td><?php echo esc_html($p->telefono); ?></td>
                            <td><?php echo esc_html((string) count($p->lineas)); ?></td>
                            <td>
                                <span class="rdt-badge <?php echo esc_attr($estadoClass); ?>">
                                    <?php echo esc_html($estadoLabel); ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo esc_url($detUrl); ?>" class="button button-small">Ver detalle</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ($paginas > 1) : ?>
            <div class="rdt-presupuestos__paginacion tablenav">
                <div class="tablenav-pages">
                    <?php if ($pagina > 1) : ?>
                        <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=' . self::SLUG . '&pag=' . ($pagina - 1))); ?>">
                            &lsaquo; Anterior
                        </a>
                    <?php endif; ?>
                    <span class="displaying-num">
                        Página <?php echo esc_html((string) $pagina); ?> de <?php echo esc_html((string) $paginas); ?>
                    </span>
                    <?php if ($pagina < $paginas) : ?>
                        <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=' . self::SLUG . '&pag=' . ($pagina + 1))); ?>">
                            Siguiente &rsaquo;
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
    }

    private function renderDetalle(int $id): void
    {
        $p = $this->repository->findById($id);

        if (!$p) {
            echo '<div class="wrap"><p>Presupuesto no encontrado.</p></div>';
            return;
        }

        $listaUrl = admin_url('admin.php?page=' . self::SLUG);
        $updated  = isset($_GET['updated']) ? (int) $_GET['updated'] : 0;
        $total    = (float) array_sum(array_map(fn($l) => $l->subtotal, $p->lineas));
        ?>
        <div class="wrap rdt-presupuestos rdt-presupuestos--detalle">
            <h1 class="wp-heading-inline">Presupuesto #<?php echo esc_html((string) $p->id); ?></h1>
            <hr class="wp-header-end">

            <p>
                <a href="<?php echo esc_url($listaUrl); ?>" class="button">
                    &larr; Volver al listado
                </a>
            </p>

            <?php if ($updated === 1) : ?>
                <div class="notice notice-success is-dismissible"><p>Cambios guardados.</p></div>
            <?php endif; ?>

            <!-- Datos del cliente -->
            <div class="rdt-presupuestos__seccion">
                <h2>Datos del cliente</h2>
                <table class="form-table rdt-presupuestos__tabla-datos">
                    <tr>
                        <th>Nombre</th>
                        <td><?php echo esc_html($p->nombre); ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo esc_html($p->email); ?></td>
                    </tr>
                    <tr>
                        <th>Teléfono</th>
                        <td><?php echo esc_html($p->telefono); ?></td>
                    </tr>
                    <tr>
                        <th>Mensaje</th>
                        <td><?php echo nl2br(esc_html($p->mensaje !== '' ? $p->mensaje : '—')); ?></td>
                    </tr>
                    <tr>
                        <th>Fecha</th>
                        <td><?php echo esc_html($p->fecha->format('d/m/Y H:i')); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Productos -->
            <div class="rdt-presupuestos__seccion">
                <h2>Productos</h2>
                <?php if (empty($p->lineas)) : ?>
                    <p><em>Sin productos.</em></p>
                <?php else : ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Precio unit.</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($p->lineas as $linea) : ?>
                            <tr>
                                <td><?php echo esc_html($linea->nombre); ?></td>
                                <td><?php echo esc_html((string) $linea->cantidad); ?></td>
                                <td>$<?php echo esc_html(number_format($linea->precioUnitario, 2, ',', '.')); ?></td>
                                <td>$<?php echo esc_html(number_format($linea->subtotal, 2, ',', '.')); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="rdt-total-label"><strong>Total estimado:</strong></td>
                                <td><strong>$<?php echo esc_html(number_format($total, 2, ',', '.')); ?></strong></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Gestión: estado y nota -->
            <div class="rdt-presupuestos__seccion rdt-presupuestos__gestion">
                <h2>Gestión</h2>

                <form method="post" action="">
                    <?php wp_nonce_field('rdt_presupuesto_accion_' . $p->id, 'rdt_nonce'); ?>
                    <input type="hidden" name="rdt_presupuesto_accion" value="guardar_estado">
                    <input type="hidden" name="presupuesto_id" value="<?php echo esc_attr((string) $p->id); ?>">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="rdt_estado">Estado</label>
                            </th>
                            <td>
                                <select name="rdt_estado" id="rdt_estado">
                                    <option value="<?php echo esc_attr(PresupuestoEstado::PENDIENTE); ?>"
                                        <?php selected($p->estado, PresupuestoEstado::PENDIENTE); ?>>
                                        Pendiente
                                    </option>
                                    <option value="<?php echo esc_attr(PresupuestoEstado::RESPONDIDO); ?>"
                                        <?php selected($p->estado, PresupuestoEstado::RESPONDIDO); ?>>
                                        Respondido
                                    </option>
                                </select>
                                <button type="submit" class="button button-secondary">Guardar estado</button>
                            </td>
                        </tr>
                    </table>
                </form>

                <form method="post" action="">
                    <?php wp_nonce_field('rdt_presupuesto_accion_' . $p->id, 'rdt_nonce'); ?>
                    <input type="hidden" name="rdt_presupuesto_accion" value="guardar_nota">
                    <input type="hidden" name="presupuesto_id" value="<?php echo esc_attr((string) $p->id); ?>">
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="rdt_nota">Nota interna</label>
                            </th>
                            <td>
                                <textarea
                                    name="rdt_nota"
                                    id="rdt_nota"
                                    rows="5"
                                    class="large-text"
                                ><?php echo esc_textarea($p->nota_interna); ?></textarea>
                                <button type="submit" class="button button-secondary">Guardar nota</button>
                            </td>
                        </tr>
                    </table>
                </form>
            </div>
        </div>
        <?php
    }
}
