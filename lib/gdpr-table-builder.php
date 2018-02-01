<?php


namespace wp_gdpr\lib;

/**
 * Class Appsaloon_Table_Builder
 * @package wp_gdpr\lib
 *
 * allows to build simple table
 */
class Gdpr_Table_Builder {

	/**
	 * @var null|string
	 */
	public $custom_classes;
	/**
	 * @var array
	 */
	public $head;
	public $data;
	public $footer;

	/**
	 * Appsaloon_Table_Builder constructor.
	 */
	public function __construct( array $head, array $data, array $footer, $custom_classes = null ) {
		if ( $custom_classes == null ) {
			$custom_classes = 'wp-list-table widefat fixed striped';
		}
		$this->custom_classes = $custom_classes;
		$this->head           = $head;
		$this->data           = $data;
		$this->footer         = $footer;
	}

	/**
	 * show table
	 */
	public function print_table() {
		$this->open_table();
		$this->build_head();
		$this->build_body();
		$this->build_footer();
		$this->close_table();
	}

	/**
	 * table open tab
	 */
	public function open_table() {
		?><table class="<?php echo $this->custom_classes; ?>"><?php
	}

	/**
	 * build head
	 */
	public function build_head() {
		if ( empty( $this->head ) ) {
			return;
		}
		?>
        <thead>
        <tr>
			<?php foreach ( $this->head as $header ) : ?>
                <th><?php echo $header; ?></th>
			<?php endforeach; ?>
        </tr>
        </thead>
		<?php
	}

	/**
	 * show body
	 */
	public function build_body() {
		?>
        <tbody>
		<?php foreach ( $this->data as $rows ) : ?>
            <tr>
				<?php foreach ( $rows as $single_row ) : ?>
                    <td><?php echo $single_row; ?></td>
				<?php endforeach; ?>
            </tr>
		<?php endforeach; ?>
        </tbody>
		<?php
	}

	/**
	 * simple footer
	 */
	public function build_footer() {
		if ( empty( $this->footer ) ) {
			return;
		}
		?>
        <tfoot>
        <tr>
            <?php
            $total_th = count($this->head);
            $colspan = '';
            if(count($this->footer) === 1){
                $colspan = ' colspan="'.$total_th.'"';
            } ?>
			<?php foreach ( $this->footer as $footer ) : ?>
                <td<?php echo $colspan; ?>><?php echo $footer; ?></td>
			<?php endforeach; ?>
        </tr>
        </tfoot>
		<?php
	}

	/**
	 * close tag of table
	 */
	public function close_table() {
		?></table><?php
	}
}
